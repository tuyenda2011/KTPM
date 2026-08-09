<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class StaffController extends Controller
{
    /**
     * Generate unique user_id with ST prefix
     */
    private function generateUserId(): string
    {
        return DB::transaction(function () {
            $maxNum = DB::table('users')
                ->where('user_id', 'like', 'ST_%')
                ->selectRaw("MAX(CAST(SUBSTRING(user_id, 4) AS UNSIGNED)) as max_num")
                ->lockForUpdate()
                ->value('max_num');

            $next = (int)($maxNum ?? 0) + 1;
            return sprintf('ST_%03d', $next);
        });
    }

    private function buildUploadFileName(string $prefix, string $employeeName, $file): string
    {
        $safeName = Str::of($employeeName)
            ->ascii()
            ->replaceMatches('/\s+/', '_')
            ->replaceMatches('/[^A-Za-z0-9_]/', '')
            ->trim('_')
            ->value();

        $safeName = $safeName !== '' ? $safeName : 'NhanVien';
        $extension = strtolower($file->getClientOriginalExtension());

        return $prefix . '_' . $safeName . '.' . $extension;
    }

    private function storeEmployeeDocument(
        string $userId,
        string $employeeName,
        $file,
        string $prefix,
        ?string $existingPath = null
    ): string {
        $directory = 'employees/' . $userId;
        $disk = Storage::disk('public');

        // Keep one document per type to avoid stale files and UI ambiguity.
        foreach ($disk->files($directory) as $path) {
            $baseName = basename($path);
            $shouldDelete = Str::startsWith($baseName, $prefix . '_');

            // Backward-compatible cleanup for legacy avatar naming.
            if (!$shouldDelete && $prefix === 'Avt') {
                $shouldDelete = Str::startsWith($baseName, 'Avatar_');
            }

            if ($shouldDelete) {
                $disk->delete($path);
            }
        }

        $fileName = $this->buildUploadFileName($prefix, $employeeName, $file);
        $newPath = $file->storeAs($directory, $fileName, 'public');

        if ($existingPath && $existingPath !== $newPath && $disk->exists($existingPath)) {
            $disk->delete($existingPath);
        }

        return $newPath;
    }

    private function resolveEmployeeFilePath(Employee $employee, string $type): ?string
    {
        $pathMap = [
            'avatar' => $employee->avatar_path,
            'cv' => $employee->cv_path,
            'contract' => $employee->contract_path,
        ];

        $path = $pathMap[$type] ?? null;
        if ($path && Storage::disk('public')->exists($path)) {
            return $path;
        }

        $directory = 'employees/' . $employee->user_id;
        if (!Storage::disk('public')->exists($directory)) {
            return null;
        }

        $files = Storage::disk('public')->files($directory);
        $patternMap = [
            'avatar' => '/(^|\\/)(Avt|Avatar)_.*\\.(jpg|jpeg|png|gif|webp)$/i',
            'cv' => '/(^|\\/)(Cv|CV)_.*\\.(pdf|doc|docx)$/i',
            'contract' => '/(^|\\/)(Ct|HD)_.*\\.(pdf|doc|docx)$/i',
        ];

        $matchedFiles = array_values(array_filter($files, function ($file) use ($patternMap, $type) {
            return preg_match($patternMap[$type], $file) === 1;
        }));

        if (empty($matchedFiles)) {
            return null;
        }

        usort($matchedFiles, function ($a, $b) {
            return Storage::disk('public')->lastModified($b) <=> Storage::disk('public')->lastModified($a);
        });

        return $matchedFiles[0];
    }

    public function departmentOverview()
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            abort(403, 'Bạn không có quyền truy cập');
        }

        $departments = Department::withCount('employees')
            ->get()
            ->map(function ($dept) {
                $activeCount = Employee::where('department_id', $dept->department_id)
                    ->where('status', 'Đang làm')
                    ->count();

                $onLeaveCount = Employee::where('department_id', $dept->department_id)
                    ->where('status', 'Tạm nghỉ')
                    ->count();

                $resignedCount = Employee::where('department_id', $dept->department_id)
                    ->where('status', 'Nghỉ việc')
                    ->count();

                return [
                    'department_id' => $dept->department_id,
                    'name' => $dept->name,
                    'description' => $dept->description,
                    'employees_count' => $dept->employees_count,
                    'active_count' => $activeCount,
                    'on_leave_count' => $onLeaveCount,
                    'resigned_count' => $resignedCount,
                ];
            });

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'Đang làm')->count();
        $onLeaveEmployees = Employee::where('status', 'Tạm nghỉ')->count();
        $resignedEmployees = Employee::where('status', 'Nghỉ việc')->count();

        return view('staff_departments', [
            'departments' => $departments,
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'onLeaveEmployees' => $onLeaveEmployees,
            'resignedEmployees' => $resignedEmployees,
        ]);
    }

    public function index(Request $request)
    {
        $departmentId = $request->get('department_id');
        $currentUser = auth()->user();

        $query = Employee::with('user', 'department')
            ->whereHas('user', function ($query) {
                $query->whereIn('role', ['staff', 'admin']);
            });

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->get();
        $departments = Department::all();
        $selectedDepartment = $departmentId
            ? Department::where('department_id', $departmentId)->first()
            : null;

        foreach ($employees as $employee) {
            $cvPath = $this->resolveEmployeeFilePath($employee, 'cv');
            $contractPath = $this->resolveEmployeeFilePath($employee, 'contract');
            $avatarPath = $this->resolveEmployeeFilePath($employee, 'avatar');
            $avatarVersion = $avatarPath ? Storage::disk('public')->lastModified($avatarPath) : null;

            $employee->cv_file = $cvPath
                ? route('staff.file', ['userId' => $employee->user_id, 'type' => 'cv'])
                : null;
            $employee->contract_file = $contractPath
                ? route('staff.file', ['userId' => $employee->user_id, 'type' => 'contract'])
                : null;
            $employee->avatar_file = $avatarPath
                ? route('staff.file', [
                    'userId' => $employee->user_id,
                    'type' => 'avatar',
                    'v' => $avatarVersion,
                ])
                : null;
        }

        return view('staff', [
            'employees' => $employees,
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
            'isDepartmentManager' => $this->isDepartmentManager($currentUser),
            'managedDepartmentId' => $this->managedDepartmentId($currentUser),
        ]);
    }

    public function show($userId)
    {
        $employee = Employee::with('user', 'department')->where('user_id', $userId)->firstOrFail();
        $currentUser = auth()->user();

        if (!$currentUser) {
            abort(403, 'Bạn không có quyền xem hồ sơ này');
        }

        $departments = Department::all();

        if (request()->wantsJson()) {
            return response()->json($employee->load('user', 'department'));
        }

        return view('staff.show', [
            'employee' => $employee,
            'departments' => $departments,
        ]);
    }

    public function serveFile(string $userId, string $type)
    {
        $employee = Employee::with('user')->where('user_id', $userId)->firstOrFail();
        $currentUser = auth()->user();

        if (!$currentUser) {
            abort(403, 'Bạn không có quyền xem tệp này');
        }

        $path = $this->resolveEmployeeFilePath($employee, $type);
        if (!$path) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function create()
    {
        $currentUser = auth()->user();


        if (!$currentUser || ($currentUser->role !== 'admin' && !$this->isDepartmentManager($currentUser))) {
            abort(403, 'Bạn không có quyền thêm nhân viên');
        }

        if ($currentUser->role === 'admin') {
            $departments = Department::all();
        } else {
            $managedDepartmentId = $this->managedDepartmentId($currentUser);
            $departments = Department::where('department_id', $managedDepartmentId)->get();
        }

        return view('staff.create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        
        $currentUser = auth()->user();

        if (!$currentUser || ($currentUser->role !== 'admin' && !$this->isDepartmentManager($currentUser))) {
            abort(403, 'Bạn không có quyền thêm nhân viên');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'phone' => 'required|string|max:20',
            'birth_date' => 'required|date',
            'gender' => 'required|in:Nam,Nữ,Khác',
            'address' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,department_id',
            'position' => 'required|string|max:100',
            'identity_card' => 'nullable|string|max:20|unique:employees,identity_card',
            'marital_status' => 'nullable|string',
            'hometown' => 'nullable|string|max:100',
            'current_address' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'status' => 'required|string|in:Đang làm,Tạm nghỉ,Nghỉ việc',
            'ethnicity' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'education_level' => 'nullable|string|max:100',
            'previous_experience' => 'nullable|string|max:2000',
            'notes' => 'nullable|string',
            'avatar_path' => 'nullable|image|max:2048',
            'avatar' => 'nullable|image|max:2048',
            'cv_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'contract_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'degree' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:255',
            'certificates' => 'nullable|string|max:2000',
            'language_certificates' => 'nullable|string|max:2000',
        ]);



        // Create User first
        $userId = $this->generateUserId();

        if ($currentUser->role !== 'admin') {
            $managedDepartmentId = $this->managedDepartmentId($currentUser);

            if ($validated['department_id'] !== $managedDepartmentId) {
                abort(403, 'Bạn chỉ được thêm nhân viên trong phòng ban mình quản lý');
            }
        }
                
        $user = User::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? ($validated['current_address'] ?? null),
        ]);

        // Handle file uploads
        $employeeData = [
            'user_id' => $userId,
            'department_id' => $validated['department_id'],
            'position' => $validated['position'],
            'identity_card' => $validated['identity_card'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'hometown' => $validated['hometown'] ?? null,
            'current_address' => $validated['current_address'] ?? null,
            'start_date' => $validated['start_date'],
            'status' => $validated['status'],
            'ethnicity' => $validated['ethnicity'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'nationality' => $validated['nationality'] ?? 'Việt Nam',
            'education_level' => $validated['education_level'] ?? null,
            'degree' => $validated['degree'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'certificates' => $validated['certificates'] ?? null,
            'language_certificates' => $validated['language_certificates'] ?? null,
            'previous_experience' => $validated['previous_experience'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if (Schema::hasColumn('employees', 'employee_code')) {
            $employeeData['employee_code'] = $userId;
        }

        if ($request->hasFile('avatar_path')) {
            $avatarFile = $request->file('avatar_path');
            $employeeData['avatar_path'] = $this->storeEmployeeDocument($userId, $validated['name'], $avatarFile, 'Avt');
        } elseif ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $employeeData['avatar_path'] = $this->storeEmployeeDocument($userId, $validated['name'], $avatarFile, 'Avt');
        }
        if ($request->hasFile('cv_path')) {
            $cvFile = $request->file('cv_path');
            $employeeData['cv_path'] = $this->storeEmployeeDocument($userId, $validated['name'], $cvFile, 'Cv');
        }
        if ($request->hasFile('contract_path')) {
            $contractFile = $request->file('contract_path');
            $employeeData['contract_path'] = $this->storeEmployeeDocument($userId, $validated['name'], $contractFile, 'Ct');
        }

        Employee::create($employeeData);

        return redirect()->route('staff.list', [
            'department_id' => $validated['department_id'],
            'selected' => $userId,
        ])->with('success', 'Thêm nhân viên thành công');
    }

    public function edit($userId)
    {
        $employee = Employee::with('user')->where('user_id', $userId)->firstOrFail();
        $currentUser = auth()->user();

        if (!$this->canEditEmployee($currentUser, $userId)) {
            abort(403, 'Bạn không có quyền chỉnh sửa nhân viên này');
        }

        if ($currentUser->role === 'admin') {
            $departments = Department::all();
        } else {
            $departments = Department::where('department_id', $employee->department_id)->get();
        }

        return view('staff.edit', [
            'employee' => $employee,
            'user' => $employee->user,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, $userId)
    {
        $employee = Employee::with('user')->where('user_id', $userId)->firstOrFail();
        $user = $employee->user;
        $currentUser = auth()->user();

        if (!$this->canEditEmployee($currentUser, $userId)) {
            abort(403, 'Bạn không có quyền cập nhật nhân viên này');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId . ',user_id',
            'phone' => 'required|string|max:20',
            'birth_date' => 'required|date',
            'gender' => 'required|in:Nam,Nữ,Khác',
            'address' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,department_id',
            'position' => 'required|string|max:100',
            'identity_card' => 'nullable|string|max:20|unique:employees,identity_card,' . $userId . ',user_id',
            'marital_status' => 'nullable|string',
            'hometown' => 'nullable|string|max:100',
            'current_address' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'status' => 'required|string|in:Đang làm,Tạm nghỉ,Nghỉ việc',
            'ethnicity' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'education_level' => 'nullable|string|max:100',
            'previous_experience' => 'nullable|string|max:2000',
            'notes' => 'nullable|string',
            'avatar_path' => 'nullable|image|max:2048',
            'avatar' => 'nullable|image|max:2048',
            'cv_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'contract_path' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'degree' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:255',
            'certificates' => 'nullable|string|max:2000',
            'language_certificates' => 'nullable|string|max:2000',
        ]);

        // Trưởng phòng sửa người khác thì chỉ được giữ nhân viên trong đúng phòng mình.
        if (
            $currentUser->role !== 'admin' &&
            $this->isDepartmentManager($currentUser) &&
            $currentUser->user_id !== $userId
        ) {
            $managedDepartmentId = $this->managedDepartmentId($currentUser);

            if ($validated['department_id'] !== $managedDepartmentId) {
                abort(403, 'Bạn chỉ được cập nhật nhân viên trong phòng ban mình quản lý');
            }
        }

        // Staff tự sửa hồ sơ của mình thì không được tự đổi phòng ban.
        if (
            $currentUser->role === 'staff' &&
            !$this->isDepartmentManager($currentUser) &&
            $currentUser->user_id === $userId
        ) {
            $validated['department_id'] = $employee->department_id;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? ($validated['current_address'] ?? $user->address),
        ]);

        $employeeData = [
            'department_id' => $validated['department_id'],
            'position' => $validated['position'],
            'marital_status' => $validated['marital_status'] ?? $employee->marital_status,
            'hometown' => $validated['hometown'] ?? $employee->hometown,
            'current_address' => $validated['current_address'] ?? $employee->current_address,
            'start_date' => $validated['start_date'],
            'status' => $validated['status'],
            'ethnicity' => $validated['ethnicity'] ?? $employee->ethnicity,
            'religion' => $validated['religion'] ?? $employee->religion,
            'nationality' => $validated['nationality'] ?? $employee->nationality,
            'education_level' => $validated['education_level'] ?? $employee->education_level,
            'previous_experience' => $validated['previous_experience'] ?? $employee->previous_experience,
            'notes' => $validated['notes'] ?? $employee->notes,
            'degree' => $validated['degree'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'certificates' => $validated['certificates'] ?? null,
            'language_certificates' => $validated['language_certificates'] ?? null,
        ];

        if (!empty($validated['identity_card'])) {
            $employeeData['identity_card'] = $validated['identity_card'];
        }

        if ($request->hasFile('avatar_path')) {
            $avatarFile = $request->file('avatar_path');
            $employeeData['avatar_path'] = $this->storeEmployeeDocument(
                $userId,
                $validated['name'],
                $avatarFile,
                'Avt',
                $employee->avatar_path
            );
        } elseif ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');
            $employeeData['avatar_path'] = $this->storeEmployeeDocument(
                $userId,
                $validated['name'],
                $avatarFile,
                'Avt',
                $employee->avatar_path
            );
        }

        if ($request->hasFile('cv_path')) {
            $cvFile = $request->file('cv_path');
            $employeeData['cv_path'] = $this->storeEmployeeDocument(
                $userId,
                $validated['name'],
                $cvFile,
                'Cv',
                $employee->cv_path
            );
        }

        if ($request->hasFile('contract_path')) {
            $contractFile = $request->file('contract_path');
            $employeeData['contract_path'] = $this->storeEmployeeDocument(
                $userId,
                $validated['name'],
                $contractFile,
                'Ct',
                $employee->contract_path
            );
        }

        $employee->update($employeeData);

        return redirect()->route('staff.list', [
            'department_id' => $employeeData['department_id'],
            'selected' => $userId,
        ])->with('success', 'Cập nhật nhân viên thành công');
    }

    public function destroy($userId)
    {
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $user = $employee->user;
        $currentUser = auth()->user();

        if (!$currentUser) {
            abort(403, 'Bạn không có quyền xóa nhân viên này');
        }

        if ($currentUser->role === 'admin') {
            // ok
        } elseif ($this->isDepartmentManager($currentUser) && $this->canManageEmployee($currentUser, $employee)) {
            // trưởng phòng được xóa nhân viên phòng mình
        } else {
            abort(403, 'Bạn không có quyền xóa nhân viên này');
        }

        $employee->delete();
        $user->delete();

        $redirectParams = [];

        if ($currentUser->role === 'admin') {
            $redirectParams = [];
        } elseif ($this->isDepartmentManager($currentUser)) {
            $redirectParams = [
                'department_id' => $this->managedDepartmentId($currentUser),
            ];
        }

        return redirect()->route('staff.list', $redirectParams)
            ->with('success', 'Xóa nhân viên thành công');
    }

    private function isDepartmentManager($user): bool
    {
        if (!$user || $user->role !== 'staff') {
            return false;
        }

        $isAssignedManager = \App\Models\Department::where('manager_user_id', $user->user_id)->exists();

        if ($isAssignedManager) {
            return true;
        }

        $position = (string) Employee::where('user_id', $user->user_id)->value('position');
        $normalizedPosition = Str::lower($position);

        return str_contains($normalizedPosition, 'truong phong')
            || str_contains($normalizedPosition, 'trưởng phòng');
    }

    private function managedDepartmentId($user): ?string
    {
        if (!$user) {
            return null;
        }

        $departmentId = \App\Models\Department::where('manager_user_id', $user->user_id)->value('department_id');

        if ($departmentId) {
            return $departmentId;
        }

        if (!$this->isDepartmentManager($user)) {
            return null;
        }

        return Employee::where('user_id', $user->user_id)->value('department_id');
    }

    private function canEditEmployee($currentUser, $targetUserId): bool
    {
        if (!$currentUser) {
            return false;
        }

        // admin sửa tất cả
        if ($currentUser->role === 'admin') {
            return true;
        }

        // staff chỉ xét tiếp nếu là staff
        if ($currentUser->role !== 'staff') {
            return false;
        }

        // staff sửa chính mình
        if ($currentUser->user_id === $targetUserId) {
            return true;
        }

        // trưởng phòng được sửa nhân viên thuộc phòng mình
        if ($this->isDepartmentManager($currentUser)) {
            $managedDepartmentId = $this->managedDepartmentId($currentUser);

            if (!$managedDepartmentId) {
                return false;
            }

            return \App\Models\Employee::where('user_id', $targetUserId)
                ->where('department_id', $managedDepartmentId)
                ->exists();
        }

        return false;
    }

    private function canManageEmployee($user, Employee $employee): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        $managedDepartmentId = $this->managedDepartmentId($user);

        return $managedDepartmentId !== null
            && $employee->department_id === $managedDepartmentId;
    }
}
