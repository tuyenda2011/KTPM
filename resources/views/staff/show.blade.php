@extends('admin')
@section('title', 'Chi Tiết Nhân Viên')
@section('content')

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Chi Tiết Nhân Viên</h2>
        <div class="flex gap-2">
            @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->user_id === $employee->user_id))
                <a href="{{ route('staff.edit', $employee->user_id) }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                    Chỉnh Sửa
                </a>
            @endif
            @if(auth()->check() && auth()->user()->role === 'admin')
                <form method="POST" action="{{ route('staff.destroy', $employee->user_id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" onclick="return confirm('Bạn chắc chắn muốn xóa nhân viên này?')">
                        Xóa
                    </button>
                </form>
            @endif
            <a href="{{ route('staff.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                Quay Lại
            </a>
        </div>
    </div>

    <!-- Avatar and Basic Info -->
    <div class="grid grid-cols-4 gap-6 mb-6">
        <div class="col-span-1">
            @if($employee->avatar_path)
                <img src="{{ asset('storage/' . $employee->avatar_path) }}" alt="avatar" class="w-full rounded border border-gray-300">
            @else
                <div class="w-full aspect-square bg-gray-200 rounded border border-gray-300 flex items-center justify-center">
                    <span class="text-gray-500">Chưa có ảnh</span>
                </div>
            @endif
        </div>
        <div class="col-span-3 space-y-3">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Họ Tên</p>
                    <p class="text-lg text-gray-900 font-semibold">{{ $employee->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Mã Nhân Viên</p>
                    <p class="text-lg text-gray-900 font-semibold">{{ $employee->employee_code ?? $employee->user_id }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Email</p>
                    <p class="text-lg text-gray-900">{{ $employee->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Số Điện Thoại</p>
                    <p class="text-lg text-gray-900">{{ $employee->user->phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Chức Vụ</p>
                    <p class="text-lg text-gray-900 font-semibold">{{ $employee->position }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Phòng Ban</p>
                    <p class="text-lg text-gray-900">{{ $employee->department->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Information -->
    <div class="grid grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="border-l-4 border-blue-500 pl-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông Tin Cá Nhân</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Ngày Sinh</p>
                    <p class="text-gray-900">{{ $employee->user->birth_date ? $employee->user->birth_date->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Giới Tính</p>
                    <p class="text-gray-900">{{ $employee->user->gender ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">CCCD</p>
                    <p class="text-gray-900">{{ $employee->identity_card ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tình Trạng Hôn Nhân</p>
                    <p class="text-gray-900">{{ $employee->marital_status ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Quê Quán</p>
                    <p class="text-gray-900">{{ $employee->hometown ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Dân Tộc</p>
                    <p class="text-gray-900">{{ $employee->ethnicity ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tôn Giáo</p>
                    <p class="text-gray-900">{{ $employee->religion ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Quốc Tịch</p>
                    <p class="text-gray-900">{{ $employee->nationality ?? 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Trình Độ Học Vấn</p>
                    <p class="text-gray-900">{{ $employee->education_level ?? 'Chưa cập nhật' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 font-medium">Bằng Cấp</p>
                    <p class="text-gray-900">{{ $employee->degree ?? 'Chưa cập nhật' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 font-medium">Trường Học</p>
                    <p class="text-gray-900">{{ $employee->school_name ?? 'Chưa cập nhật' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 font-medium">Chứng Chỉ</p>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $employee->certificates ?? 'Chưa cập nhật' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 font-medium">Bằng Ngôn Ngữ</p>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $employee->language_certificates ?? 'Chưa cập nhật' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 font-medium">Kinh Nghiệm Việc Làm Trước Đây</p>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $employee->previous_experience ?? 'Chưa cập nhật' }}</p>
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="border-l-4 border-green-500 pl-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông Tin Công Ty</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tình Trạng</p>
                    <p class="text-gray-900">
                        <span class="badge {{ $employee->status === 'Đang làm' ? 'dang-lam' : ($employee->status === 'Tạm nghỉ' ? 'tam-nghi' : 'nghi-viec') }}">
                            {{ $employee->status }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Ngày Bắt Đầu</p>
                    <p class="text-gray-900">{{ $employee->start_date ? $employee->start_date->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Địa Chỉ Hiện Tại</p>
                    <p class="text-gray-900">{{ $employee->current_address ?? 'Chưa cập nhật' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Files Section -->
    @if($employee->cv_path || $employee->contract_path)
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tài Liệu</h3>
        <div class="grid grid-cols-2 gap-4">
            @if($employee->cv_path)
            <a href="{{ asset('storage/' . $employee->cv_path) }}" target="_blank" class="flex items-center gap-2 p-4 border border-gray-300 rounded hover:bg-gray-50 transition">
                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">CV Nhân Viên</p>
                    <p class="text-sm text-gray-500">Tải xuống CV</p>
                </div>
            </a>
            @endif
            @if($employee->contract_path)
            <a href="{{ asset('storage/' . $employee->contract_path) }}" target="_blank" class="flex items-center gap-2 p-4 border border-gray-300 rounded hover:bg-gray-50 transition">
                <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Hợp Đồng Lao Động</p>
                    <p class="text-sm text-gray-500">Tải xuống hợp đồng</p>
                </div>
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Notes Section -->
    @if($employee->notes)
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Ghi Chú</h3>
        <p class="text-gray-700 whitespace-pre-wrap">{{ $employee->notes }}</p>
    </div>
    @endif
</div>

<style>
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .badge.dang-lam {
        background-color: #c6f6d5;
        color: #22543d;
    }
    
    .badge.tam-nghi {
        background-color: #feebc8;
        color: #7c2d12;
    }
    
    .badge.nghi-viec {
        background-color: #fed7d7;
        color: #742a2a;
    }
</style>

@endsection
