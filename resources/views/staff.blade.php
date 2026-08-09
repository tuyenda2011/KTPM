@extends('admin')
@section('title', 'Staff Management')
@section('content')

<div class="flex gap-6 h-[calc(100vh-10rem)]" style="margin-left: -27px; padding-left: 5px;">
    <!-- Employee List Sidebar -->
    <div class="w-80 bg-white rounded-lg shadow p-4 overflow-y-auto">
        <div class="mb-4">
            <a href="{{ route('staff.index') }}"
            class="w-full block text-center px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm font-medium transition-colors mb-4">
                ← Quay lại danh sách phòng ban
            </a>

            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Bộ lọc & Tìm kiếm</h3>
                <button onclick="resetFilters()"
                        class="px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 text-sm font-medium transition-colors">
                    Làm mới
                </button>
            </div>
        </div>
        
        <!-- Search -->
        <input type="text" id="searchInput" placeholder="Tìm kiếm theo tên/MNV" class="w-full p-2 mb-4 border border-gray-300 rounded" onkeyup="filterAndSortEmployees()">
        
        
        <!-- Status Filter -->
        <select class="w-full p-2 mb-4 border border-gray-300 rounded" id="statusFilter" onchange="filterAndSortEmployees()">
            <option value="">Trạng thái</option>
            <option value="Đang làm">Đang làm</option>
            <option value="Tạm nghỉ">Tạm nghỉ</option>
            <option value="Nghỉ việc">Nghỉ việc</option>
        </select>
        
        <!-- Sort -->
        <div class="flex gap-2">
            <select class="flex-1 p-2 mb-4 border border-gray-300 rounded" id="sortBy" onchange="filterAndSortEmployees()">
                <option value="">Sắp xếp</option>
                <option value="name_asc">Tên (A-Z)</option>
                <option value="name_desc">Tên (Z-A)</option>
                <option value="user_id_asc">Mã (↑)</option>
                <option value="user_id_desc">Mã (↓)</option>
                <option value="start_date_asc">Ngày làm (cũ nhất)</option>
                <option value="start_date_desc">Ngày làm (mới nhất)</option>
            </select>
        </div>
        
        <ul class="employee-list space-y-2" id="employeeListContainer">
            @forelse($employees as $employee)
            <li onclick="selectEmployee(this, '{{ $employee->user_id }}')" class="p-3 border-b border-gray-200 cursor-pointer hover:bg-gray-50 rounded">
                <div class="flex items-center gap-3">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 35 35'%3E%3Ccircle cx='17.5' cy='17.5' r='17.5' fill='%23e5e7eb'/%3E%3C/svg%3E" data-avatar="{{ $employee->avatar_file ?? '' }}" alt="avatar" class="w-9 h-9 rounded-full avatar-thumb">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">{{ $employee->user->name }}</div>
                        <small class="text-gray-500">{{ $employee->employee_code ?? $employee->user_id }}</small>
                        <span class="badge {{ $employee->status === 'Đang làm' ? 'dang-lam' : ($employee->status === 'Tạm nghỉ' ? 'tam-nghi' : 'nghi-viec') }} ml-2 inline-block">{{ $employee->status }}</span>
                    </div>
                </div>
            </li>
            @empty
            <li class="p-3 text-gray-500 text-center">Chưa có nhân viên</li>
            @endforelse
        </ul>
    </div>

    <!-- Employee Detail Form -->
    <div class="flex-1 bg-white rounded-lg shadow p-6 overflow-y-auto">
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <div class="mb-6">
                    @if($selectedDepartment)
                        <h1 class="text-3xl font-bold text-blue-600 mb-2">
                            {{ $selectedDepartment->name }}
                        </h1>
                    @else
                        <h1 class="text-3xl font-bold text-blue-600 mb-2">
                            Tất cả nhân viên
                        </h1>
                    @endif

                    <h2 class="text-xl font-semibold text-gray-800">
                        Chi tiết nhân viên
                    </h2>
                </div>

                <div class="flex gap-2" id="actionButtons">
                    @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                    @php
                        $user = auth()->user();
                    @endphp

                    @if($user && $user->role === 'admin')
                        <a href="{{ route('staff.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">Thêm mới</a>
                    @elseif($user && $isDepartmentManager && $selectedDepartment && $selectedDepartment->department_id === $managedDepartmentId)
                        <a href="{{ route('staff.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">Thêm mới</a>
                    @endif
                    <a id="editBtn" href="#" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors hidden">Chỉnh sửa</a>
                    <button type="button" id="deleteBtn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors hidden">Xóa</button>
                    @elseif(auth()->check() && auth()->user()->role === 'staff')
                    <a id="editBtn" href="#" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors hidden">Chỉnh sửa</a>
                    @endif
                </div>
            </div>
            <div class="flex gap-6 items-start">
                <img id="empAvatar" src="{{ isset($employees) && count($employees) > 0 ? ($employees[0]->avatar_file ?: 'https://via.placeholder.com/150x180') : 'https://via.placeholder.com/150x180' }}" alt="ảnh nhân viên" class="w-32 h-44 border border-gray-300 rounded">
                <form id="employeeForm" method="POST" action="" enctype="multipart/form-data" class="flex-1 space-y-6 mt-0">
                    @csrf
                    <input type="hidden" id="employeeId" name="user_id" value="">

                    {{-- 1. THÔNG TIN CƠ BẢN --}}
                    <div class="border rounded-lg px-6 pt-4 pb-6 bg-gray-50">
                        <h3 class="text-2xl font-bold text-black mb-4">Thông tin cơ bản</h3>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                                <input type="text" id="name" name="name" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã nhân viên</label>
                                <input type="text" id="employeeIdDisplay" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh</label>
                                <input type="date" id="birth_date" name="birth_date" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính</label>
                                <input type="text" id="gender" name="gender" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CCCD</label>
                                <input type="text" id="identityCard" name="identity_card" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tình trạng hôn nhân</label>
                                <input type="text" id="maritalStatus" name="marital_status" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quê quán</label>
                                <input type="text" id="hometown" name="hometown" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SĐT</label>
                                <input type="text" id="phone" name="phone" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ hiện tại</label>
                                <input type="text" id="currentAddress" name="current_address" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dân tộc</label>
                                <input type="text" id="ethnicity" name="ethnicity" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tôn giáo</label>
                                <input type="text" id="religion" name="religion" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quốc tịch</label>
                                <input type="text" id="nationality" name="nationality" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- 2. THÔNG TIN CÔNG VIỆC --}}
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-2xl font-bold text-black mb-4">Thông tin công việc</h3>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phòng ban</label>
                                <input type="text" id="deptName" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Chức vụ</label>
                                <input type="text" id="position" name="position" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày làm việc</label>
                                <input type="date" id="startDate" name="start_date" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                <input type="text" id="status" name="status" class="w-full p-2 border border-gray-300 rounded bg-green-100" readonly>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full p-2 border border-gray-300 rounded" readonly></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hợp đồng</label>
                                <a id="contractLink" href="#" target="_blank" class="w-full p-2 bg-blue-500 text-white rounded hover:bg-blue-600 inline-block text-center transition-colors">
                                    Đang tải...
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 3. THÔNG TIN HỌC VẤN - KINH NGHIỆM --}}
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-2xl font-bold text-black mb-4">Thông tin học vấn - kinh nghiệm</h3>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trình độ học vấn</label>
                                <input type="text" id="educationLevel" name="education_level" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bằng cấp</label>
                                <input type="text" id="degree" name="degree" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trường học</label>
                                <input type="text" id="schoolName" name="school_name" class="w-full p-2 border border-gray-300 rounded" readonly>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Chứng chỉ</label>
                                <textarea id="certificates" name="certificates" rows="3" class="w-full p-2 border border-gray-300 rounded" readonly></textarea>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bằng ngôn ngữ</label>
                                <textarea id="languageCertificates" name="language_certificates" rows="3" class="w-full p-2 border border-gray-300 rounded" readonly></textarea>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kinh nghiệm việc làm trước đây</label>
                                <textarea id="previousExperience" name="previous_experience" rows="3" class="w-full p-2 border border-gray-300 rounded" readonly></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CV</label>
                                <a id="cvLink" href="#" target="_blank" class="w-full p-2 bg-blue-500 text-white rounded hover:bg-blue-600 inline-block text-center transition-colors">
                                    Đang tải...
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Employees data injected from server
const employeesData = @json($employees);

function selectDepartment(button, departmentId) {
    selectedDepartment = departmentId;

    document.querySelectorAll('.department-btn').forEach(btn => {
        btn.classList.remove('bg-blue-500', 'text-white', 'font-medium');
        btn.classList.add('bg-white');
    });

    button.classList.remove('bg-white');
    button.classList.add('bg-blue-500', 'text-white', 'font-medium');

    filterAndSortEmployees();
}

function filterAndSortEmployees() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const deptFilter = '';
    const statusFilter = document.getElementById('statusFilter').value;
    const sortBy = document.getElementById('sortBy').value;

    let filtered = employeesData.filter(emp => {
        const matchSearch = !searchText ||
            emp.user?.name?.toLowerCase().includes(searchText) ||
            (emp.employee_code || emp.user_id || '').toLowerCase().includes(searchText);

        const matchDept = !deptFilter || emp.department_id === deptFilter;
        const matchStatus = !statusFilter || emp.status === statusFilter;

        return matchSearch && matchDept && matchStatus;
    });

    if (sortBy) {
        filtered.sort((a, b) => {
            let aVal = '', bVal = '';

            if (sortBy.startsWith('name')) {
                aVal = a.user?.name || '';
                bVal = b.user?.name || '';
            } else if (sortBy.startsWith('user_id')) {
                aVal = a.employee_code || a.user_id || '';
                bVal = b.employee_code || b.user_id || '';
            } else if (sortBy.startsWith('start_date')) {
                aVal = a.start_date || '';
                bVal = b.start_date || '';
            }

            if (sortBy.endsWith('_asc')) {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });
    }

    const container = document.getElementById('employeeListContainer');
    const visibleCountEl = document.getElementById('visibleCount');

    if (visibleCountEl) {
        visibleCountEl.textContent = filtered.length;
    }

    if (filtered.length === 0) {
        container.innerHTML = '<li class="p-3 text-gray-500 text-center">Không tìm thấy nhân viên</li>';
        return;
    }

    container.innerHTML = filtered.map(emp => `
        <li onclick="selectEmployee(this, '${emp.user_id}')" class="p-3 border-b border-gray-200 cursor-pointer hover:bg-gray-50 rounded">
            <div class="flex items-center gap-3">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 35 35'%3E%3Ccircle cx='17.5' cy='17.5' r='17.5' fill='%23e5e7eb'/%3E%3C/svg%3E"
                     data-avatar="${emp.avatar_file || ''}"
                     alt="avatar"
                     class="w-9 h-9 rounded-full avatar-thumb">
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">${emp.user?.name || ''}</div>
                    <small class="text-gray-500">${emp.employee_code || emp.user_id || ''}</small>
                    <span class="badge ${emp.status === 'Đang làm' ? 'dang-lam' : (emp.status === 'Tạm nghỉ' ? 'tam-nghi' : 'nghi-viec')} ml-2 inline-block">${emp.status}</span>
                </div>
            </div>
        </li>
    `).join('');
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('sortBy').value = '';

    filterAndSortEmployees();
}

function openTab(evt, tabName) {
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.add('hidden'));
    document.getElementById(tabName).classList.remove('hidden');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('border-blue-500', 'text-blue-500'));
    evt.currentTarget.classList.add('border-blue-500', 'text-blue-500');
}

function selectEmployee(el, userId) {
    // Remove previous selection
    document.querySelectorAll('.employee-list li').forEach(li => li.classList.remove('bg-blue-50'));
    el.classList.add('bg-blue-50');
    
    // Get current user info from Laravel
    const currentUserRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content') || 'guest';
    const currentUserId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || '';
    
    // Get buttons
    const editBtn = document.getElementById('editBtn');
    const deleteBtn = document.getElementById('deleteBtn');
    
    // Show/hide buttons based on user role
    if (editBtn) {
        const isManager = @json($isDepartmentManager ?? false);
        const managedDepartmentId = @json($managedDepartmentId ?? null);
        const targetEmployee = employeesData.find(e => e.user_id === userId);

        if (currentUserRole === 'admin') {
            editBtn.classList.remove('hidden');
            editBtn.href = `/staff/${userId}/edit`;
        } else if (currentUserRole === 'staff' && currentUserId === userId) {
            editBtn.classList.remove('hidden');
            editBtn.href = `/staff/${userId}/edit`;
        } else if (
            currentUserRole === 'staff' &&
            isManager &&
            targetEmployee &&
            targetEmployee.department_id === managedDepartmentId
        ) {
            editBtn.classList.remove('hidden');
            editBtn.href = `/staff/${userId}/edit`;
        } else {
            editBtn.classList.add('hidden');
        }
    }
    
    if (deleteBtn) {
        const isManager = @json($isDepartmentManager ?? false);
        const managedDepartmentId = @json($managedDepartmentId ?? null);
        const targetEmployee = employeesData.find(e => e.user_id === userId);

        if (currentUserRole === 'admin') {
            deleteBtn.classList.remove('hidden');
            deleteBtn.onclick = function(e) {
                e.preventDefault();
                if (confirm('Bạn chắc chắn muốn xóa nhân viên này?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/staff/${userId}`;

                    let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!token) {
                        token = document.querySelector('input[name="_token"]')?.value;
                    }

                    if (token) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = token;
                        form.appendChild(csrfInput);
                    }

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            };
        } else if (
            currentUserRole === 'staff' &&
            isManager &&
            targetEmployee &&
            targetEmployee.department_id === managedDepartmentId
        ) {
            deleteBtn.classList.remove('hidden');
            deleteBtn.onclick = function(e) {
                e.preventDefault();
                if (confirm('Bạn chắc chắn muốn xóa nhân viên này?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/staff/${userId}`;

                    let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!token) {
                        token = document.querySelector('input[name="_token"]')?.value;
                    }

                    if (token) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = token;
                        form.appendChild(csrfInput);
                    }

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            };
        } else {
            deleteBtn.classList.add('hidden');
        }
    }
    
    // Get employee data from injected data
    const emp = employeesData.find(e => e.user_id === userId);
    if (!emp) {
        alert('Không tìm thấy nhân viên');
        return;
    }
    
    const user = emp.user || {};
    const dept = emp.department || {};
    
    // Populate form
    const fields = {
        'employeeId': user.user_id || '',
        'name': user.name || '',
        'employeeIdDisplay': emp.employee_code || user.user_id || '',
        'deptName': dept.name || '',
        'position': emp.position || '',
        'identityCard': emp.identity_card || '',
        'birth_date': user.birth_date ? user.birth_date.split('T')[0] : '',
        'gender': user.gender || '',
        'maritalStatus': emp.marital_status || '',
        'hometown': emp.hometown || '',
        'phone': user.phone || '',
        'email': user.email || '',
        'currentAddress': emp.current_address || '',
        'startDate': emp.start_date ? emp.start_date.split('T')[0] : '',
        'status': emp.status || '',
        'ethnicity': emp.ethnicity || '',
        'religion': emp.religion || '',
        'nationality': emp.nationality || '',
        'educationLevel': emp.education_level || '',
        'degree': emp.degree || '',
        'schoolName': emp.school_name || '',
        'certificates': emp.certificates || '',
        'languageCertificates': emp.language_certificates || '',
        'previousExperience': emp.previous_experience || '',
        'notes': emp.notes || ''
    };

    Object.entries(fields).forEach(([fieldId, value]) => {
        const el = document.getElementById(fieldId);
        if (el) {
            el.value = value;
        }
    });
    
    // Update avatar
    const avatarEl = document.getElementById('empAvatar');
    if (emp.avatar_file && avatarEl) {
        avatarEl.src = emp.avatar_file;
    } else if (avatarEl) {
        avatarEl.src = 'https://via.placeholder.com/150x180';
    }
    
    // Lazy load avatars in the list after a short delay
    setTimeout(() => {
        document.querySelectorAll('img.avatar-thumb[data-avatar]').forEach(img => {
            if (img.dataset.avatar && !img.src.includes('/storage/')) {
                img.src = img.dataset.avatar;
                img.removeAttribute('data-avatar');
            }
        });
    }, 300);
    
    // Update file links from pre-computed data
    const cvLink = document.getElementById('cvLink');
    const contractLink = document.getElementById('contractLink');
    
    // Debug: Log employee data
    console.log('Employee:', emp);
    console.log('CV file path:', emp.cv_file);
    console.log('Contract file path:', emp.contract_file);
    
    // Set CV link based on pre-computed cv_file
    if (cvLink) {
        if (emp.cv_file) {
            cvLink.href = emp.cv_file;
            cvLink.textContent = 'Tải CV';
            cvLink.classList.remove('opacity-50', 'cursor-not-allowed');
            cvLink.style.pointerEvents = 'auto';
            cvLink.onclick = null;
        } else {
            cvLink.href = '#';
            cvLink.textContent = 'Chưa có CV';
            cvLink.classList.add('opacity-50', 'cursor-not-allowed');
            cvLink.style.pointerEvents = 'none';
            cvLink.onclick = (e) => e.preventDefault();
        }
    }
    
    // Set Contract link based on pre-computed contract_file
    if (contractLink) {
        if (emp.contract_file) {
            contractLink.href = emp.contract_file;
            contractLink.textContent = 'Tải Hợp đồng';
            contractLink.classList.remove('opacity-50', 'cursor-not-allowed');
            contractLink.style.pointerEvents = 'auto';
            contractLink.onclick = null;
        } else {
            contractLink.href = '#';
            contractLink.textContent = 'Chưa có Hợp đồng';
            contractLink.classList.add('opacity-50', 'cursor-not-allowed');
            contractLink.style.pointerEvents = 'none';
            contractLink.onclick = (e) => e.preventDefault();
        }
    }
    
    console.log('Form populated successfully');
}

// Populate first employee on load
document.addEventListener('DOMContentLoaded', function() {
    filterAndSortEmployees();

    const params = new URLSearchParams(window.location.search);
    const selectedUserId = params.get('selected');

    let targetEmployee = null;

    if (selectedUserId) {
        targetEmployee = Array.from(document.querySelectorAll('.employee-list li')).find(li => {
            const onclickValue = li.getAttribute('onclick') || '';
            return onclickValue.includes(`'${selectedUserId}'`);
        });
    }

    const firstEmployee = targetEmployee || document.querySelector('.employee-list li');

    if (firstEmployee) {
        firstEmployee.click();
    }
});

</script>

<style>
    /* Ensure buttons show properly */
    #editBtn:not(.hidden),
    #deleteBtn:not(.hidden) {
        display: inline-block !important;
    }
    
    #editBtn.hidden,
    #deleteBtn.hidden {
        display: none !important;
    }
</style>

@endsection
