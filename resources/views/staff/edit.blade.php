@extends('admin')
@section('title', 'Chỉnh Sửa Nhân Viên')
@section('content')

<div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Chỉnh Sửa Nhân Viên</h2>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Lỗi:</strong>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.update', $employee->user_id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- 1. THÔNG TIN CƠ BẢN --}}
        <div class="border rounded-lg p-6 bg-gray-50">
            <h3 class="text-2xl font-bold text-black mb-4">Thông tin cơ bản</h3>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Họ Tên *</label>
                    <input type="text" name="name" class="w-full p-2 border border-gray-300 rounded @error('name') border-red-500 @enderror" value="{{ old('name', $employee->user->name) }}" required>
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày Sinh</label>
                    <input type="date" name="birth_date" class="w-full p-2 border border-gray-300 rounded @error('birth_date') border-red-500 @enderror" value="{{ old('birth_date', $employee->user->birth_date?->format('Y-m-d')) }}">
                    @error('birth_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giới Tính</label>
                    <select name="gender" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">-- Chọn --</option>
                        <option value="Nam" {{ old('gender', $employee->user->gender) == 'Nam' ? 'selected' : '' }}>Nam</option>
                        <option value="Nữ" {{ old('gender', $employee->user->gender) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                        <option value="Khác" {{ old('gender', $employee->user->gender) == 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CCCD</label>
                    <input type="text" name="identity_card" class="w-full p-2 border border-gray-300 rounded @error('identity_card') border-red-500 @enderror" value="{{ old('identity_card', $employee->identity_card) }}">
                    @error('identity_card') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tình Trạng Hôn Nhân</label>
                    <select name="marital_status" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">-- Chọn --</option>
                        <option value="Độc thân" {{ old('marital_status', $employee->marital_status) == 'Độc thân' ? 'selected' : '' }}>Độc thân</option>
                        <option value="Đã kết hôn" {{ old('marital_status', $employee->marital_status) == 'Đã kết hôn' ? 'selected' : '' }}>Đã kết hôn</option>
                        <option value="Ly hôn" {{ old('marital_status', $employee->marital_status) == 'Ly hôn' ? 'selected' : '' }}>Ly hôn</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quê Quán</label>
                    <input type="text" name="hometown" class="w-full p-2 border border-gray-300 rounded @error('hometown') border-red-500 @enderror" value="{{ old('hometown', $employee->hometown) }}">
                    @error('hometown') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded @error('email') border-red-500 @enderror" value="{{ old('email', $employee->user->email) }}" required>
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số Điện Thoại *</label>
                    <input type="tel" name="phone" class="w-full p-2 border border-gray-300 rounded @error('phone') border-red-500 @enderror" value="{{ old('phone', $employee->user->phone) }}" required>
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Địa Chỉ Hiện Tại</label>
                    <input type="text" name="current_address" class="w-full p-2 border border-gray-300 rounded @error('current_address') border-red-500 @enderror" value="{{ old('current_address', $employee->current_address) }}">
                    @error('current_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dân Tộc</label>
                    <input type="text" name="ethnicity" class="w-full p-2 border border-gray-300 rounded @error('ethnicity') border-red-500 @enderror" value="{{ old('ethnicity', $employee->ethnicity) }}">
                    @error('ethnicity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tôn Giáo</label>
                    <input type="text" name="religion" class="w-full p-2 border border-gray-300 rounded @error('religion') border-red-500 @enderror" value="{{ old('religion', $employee->religion) }}">
                    @error('religion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quốc Tịch</label>
                    <input type="text" name="nationality" class="w-full p-2 border border-gray-300 rounded @error('nationality') border-red-500 @enderror" value="{{ old('nationality', $employee->nationality) }}">
                    @error('nationality') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh Đại Diện</label>
                    @if($employee->avatar_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $employee->avatar_path) }}" alt="avatar" class="w-24 h-24 rounded object-cover border">
                        </div>
                    @endif
                    <input type="file" name="avatar" class="w-full p-2 border border-gray-300 rounded @error('avatar') border-red-500 @enderror" accept="image/*">
                    @error('avatar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- 2. THÔNG TIN CÔNG VIỆC --}}
        <div class="border rounded-lg p-6 bg-gray-50">
            <h3 class="text-2xl font-bold text-black mb-4">Thông tin công việc</h3>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chức Vụ *</label>
                    <input type="text" name="position" class="w-full p-2 border border-gray-300 rounded @error('position') border-red-500 @enderror" value="{{ old('position', $employee->position) }}" required>
                    @error('position') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phòng Ban *</label>
                    <select name="department_id" class="w-full p-2 border border-gray-300 rounded @error('department_id') border-red-500 @enderror" required>
                        <option value="">-- Chọn Phòng Ban --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ old('department_id', $employee->department_id) == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tình Trạng *</label>
                    <select name="status" class="w-full p-2 border border-gray-300 rounded @error('status') border-red-500 @enderror" required>
                        <option value="">-- Chọn --</option>
                        <option value="Đang làm" {{ old('status', $employee->status) == 'Đang làm' ? 'selected' : '' }}>Đang làm</option>
                        <option value="Tạm nghỉ" {{ old('status', $employee->status) == 'Tạm nghỉ' ? 'selected' : '' }}>Tạm nghỉ</option>
                        <option value="Nghỉ việc" {{ old('status', $employee->status) == 'Nghỉ việc' ? 'selected' : '' }}>Nghỉ việc</option>
                    </select>
                    @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày Bắt Đầu</label>
                    <input type="date" name="start_date" class="w-full p-2 border border-gray-300 rounded @error('start_date') border-red-500 @enderror" value="{{ old('start_date', $employee->start_date?->format('Y-m-d')) }}">
                    @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hợp Đồng</label>
                    @if($employee->contract_path)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $employee->contract_path) }}" target="_blank" class="text-blue-500 hover:underline">Xem hợp đồng hiện tại</a>
                        </div>
                    @endif
                    <input type="file" name="contract_path" class="w-full p-2 border border-gray-300 rounded @error('contract_path') border-red-500 @enderror">
                    @error('contract_path') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi Chú</label>
                    <textarea name="notes" rows="4" class="w-full p-2 border border-gray-300 rounded @error('notes') border-red-500 @enderror">{{ old('notes', $employee->notes) }}</textarea>
                    @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- 3. THÔNG TIN HỌC VẤN - KINH NGHIỆM --}}
        <div class="border rounded-lg p-6 bg-gray-50">
            <h3 class="text-2xl font-bold text-black mb-4">Thông tin học vấn - kinh nghiệm</h3>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trình Độ Học Vấn</label>
                    <input type="text" name="education_level" class="w-full p-2 border border-gray-300 rounded @error('education_level') border-red-500 @enderror" placeholder="VD: Đại học, Cao đẳng..." value="{{ old('education_level', $employee->education_level) }}">
                    @error('education_level') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bằng Cấp</label>
                    <input type="text" name="degree" class="w-full p-2 border border-gray-300 rounded @error('degree') border-red-500 @enderror" placeholder="VD: Cử nhân CNTT, Thạc sĩ..." value="{{ old('degree', $employee->degree) }}">
                    @error('degree') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trường Học</label>
                    <input type="text" name="school_name" class="w-full p-2 border border-gray-300 rounded @error('school_name') border-red-500 @enderror" placeholder="VD: Đại học Bách Khoa Hà Nội" value="{{ old('school_name', $employee->school_name) }}">
                    @error('school_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chứng Chỉ</label>
                    <textarea name="certificates" rows="3" class="w-full p-2 border border-gray-300 rounded @error('certificates') border-red-500 @enderror" placeholder="VD: MOS, CCNA, PMP...">{{ old('certificates', $employee->certificates) }}</textarea>
                    @error('certificates') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bằng Ngôn Ngữ</label>
                    <textarea name="language_certificates" rows="3" class="w-full p-2 border border-gray-300 rounded @error('language_certificates') border-red-500 @enderror" placeholder="VD: TOEIC 750, IELTS 6.5, TOCFL B1...">{{ old('language_certificates', $employee->language_certificates) }}</textarea>
                    @error('language_certificates') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kinh Nghiệm Việc Làm Trước Đây</label>
                    <textarea name="previous_experience" rows="3" class="w-full p-2 border border-gray-300 rounded @error('previous_experience') border-red-500 @enderror" placeholder="Mô tả ngắn kinh nghiệm việc làm trước đây...">{{ old('previous_experience', $employee->previous_experience) }}</textarea>
                    @error('previous_experience') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CV</label>
                    @if($employee->cv_path)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $employee->cv_path) }}" target="_blank" class="text-blue-500 hover:underline">Xem CV hiện tại</a>
                        </div>
                    @endif
                    <input type="file" name="cv_path" class="w-full p-2 border border-gray-300 rounded @error('cv_path') border-red-500 @enderror">
                    @error('cv_path') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-4 mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                Cập Nhật Nhân Viên
            </button>
            <a href="{{ route('staff.index') }}" class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                Hủy
            </a>
        </div>
    </form>
</div>

@endsection