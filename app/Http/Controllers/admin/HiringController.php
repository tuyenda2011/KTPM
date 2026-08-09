<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Notifications\CandidatePromotedToStaffNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HiringController extends Controller
{
    private function generateEmployeeCode(): string
    {
        $maxFromUsers = DB::table('users')
            ->where('user_id', 'like', 'ST_%')
            ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(user_id, 4) AS UNSIGNED)), 0) as max_num")
            ->lockForUpdate()
            ->value('max_num');

        $maxFromEmployeeCodes = 0;
        if (Schema::hasColumn('employees', 'employee_code')) {
            $maxFromEmployeeCodes = DB::table('employees')
                ->where('employee_code', 'like', 'ST_%')
                ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(employee_code, 4) AS UNSIGNED)), 0) as max_num")
                ->lockForUpdate()
                ->value('max_num');
        }

        $next = max((int) $maxFromUsers, (int) $maxFromEmployeeCodes) + 1;

        return sprintf('ST_%03d', $next);
    }

    private function recruitmentRedirectParams(Request $request): array
    {
        $params = ['tab' => $request->input('tab', 'hiring')];
        $periodId = $request->input('period_id');

        if (!empty($periodId)) {
            $params['period_id'] = $periodId;
        }

        return $params;
    }

    public function index()
    {
        $candidates = Candidate::with([
            'user',
            'job',
            'interviews' => fn ($query) => $query->orderByDesc('scheduled_at'),
        ])
            ->whereHas('interviews', fn ($query) => $query->where('result', 'pass'))
            ->orderByDesc('updated_at')
            ->paginate(10);

        $departments = Department::orderBy('name')->get();

        return view('hiring_promotions', [
            'candidates' => $candidates,
            'departments' => $departments,
        ]);
    }

    public function promote(Request $request, string $candidateId)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,department_id',
            'position' => 'nullable|string|max:100',
        ]);

        $candidate = Candidate::with(['user', 'interviews', 'user.employee'])
            ->where('user_id', $candidateId)
            ->firstOrFail();

        $hasPassInterview = $candidate->interviews->contains(fn ($interview) => $interview->result === 'pass');
        if (!$hasPassInterview) {
            return redirect()->route('recruitment.index', $this->recruitmentRedirectParams($request))->withErrors([
                'promote' => 'Ứng viên này chưa có kết quả phỏng vấn đạt, không thể nâng role.',
            ]);
        }

        if (!$candidate->user) {
            return redirect()->route('recruitment.index', $this->recruitmentRedirectParams($request))->withErrors([
                'promote' => 'Không tìm thấy tài khoản ứng viên để nâng role.',
            ]);
        }

        DB::transaction(function () use ($candidate, $validated) {
            $user = $candidate->user;
            $employee = $user->employee;

            if ($user->role !== 'staff') {
                $user->update(['role' => 'staff']);
            }

            $position = $validated['position']
                ?? $employee?->position
                ?? $candidate->position_applied
                ?? 'Nhân viên';

            $departmentId = array_key_exists('department_id', $validated)
                ? ($validated['department_id'] ?: $employee?->department_id)
                : $employee?->department_id;

            $promotionNote = 'Nâng role từ ứng viên vào lúc ' . now()->format('d/m/Y H:i');
            $mergedNotes = trim(($employee?->notes ? $employee->notes . "\n" : '') . $promotionNote);

            $employeePayload = [
                'department_id' => $departmentId,
                'position' => $position,
                'start_date' => $employee?->start_date?->toDateString() ?? now()->toDateString(),
                'status' => 'Đang làm',
                'notes' => $mergedNotes,
            ];

            if (Schema::hasColumn('employees', 'employee_code')) {
                $employeePayload['employee_code'] = $employee?->employee_code ?: $this->generateEmployeeCode();
            }

            Employee::updateOrCreate(
                ['user_id' => $user->user_id],
                $employeePayload
            );

            $candidate->update([
                'status' => 'Đã nhận việc',
            ]);

            $departmentName = null;
            if (!empty($departmentId)) {
                $departmentName = Department::where('department_id', $departmentId)->value('name');
            }

            $user->notify(new CandidatePromotedToStaffNotification($position, $departmentName));
        });

        return redirect()->route('recruitment.index', $this->recruitmentRedirectParams($request))
            ->with('success', 'Đã nâng role ứng viên thành nhân viên thành công.');
    }
}
