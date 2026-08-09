<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Công ty TNHH THT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ url('css/app.css') }}">
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center px-4 py-8">

    <div class="register-card rounded-2xl shadow-2xl p-8 w-full max-w-lg">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full gradient-bg flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold logo-text mb-2">Tạo tài khoản mới</h1>
            <p class="text-gray-600">Tham gia hệ thống Công ty TNHH THT</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex justify-center mb-8">
            <div class="flex items-center space-x-4">
                <div class="step-indicator active w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">1</div>
                <div class="w-12 h-1 bg-gray-200 rounded"></div>
                <div class="step-indicator w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-500">2</div>
                <div class="w-12 h-1 bg-gray-200 rounded"></div>
                <div class="step-indicator w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-500">3</div>
            </div>
        </div>

        <!-- Form -->
        <form id="registerForm" method="POST" action="{{ route('auth.register.submit') }}" class="space-y-6">
            @csrf

            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Step 1 -->
            <div id="step1" class="step-content">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin cá nhân</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Họ *</label>
                        <input type="text" name="firstName" value="{{ old('firstName') }}" required
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                            placeholder="Nhập họ">
                        @error('firstName')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên *</label>
                        <input type="text" name="lastName" value="{{ old('lastName') }}" required
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                            placeholder="Nhập tên">
                        @error('lastName')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ngày sinh</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                        class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none">
                    @error('birth_date')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Giới tính</label>
                    <select name="gender" value="{{ old('gender') }}" required
                        class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none">
                        <option value="">Chọn giới tính</option>
                        <option value="Nam" {{ old('gender') === 'Nam' ? 'selected' : '' }}>Nam</option>
                        <option value="Nữ" {{ old('gender') === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                        <option value="Khác" {{ old('gender') === 'Khác' ? 'selected' : '' }}>Khác</option>
                    </select>
                    @error('gender')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Step 2 -->
            <div id="step2" class="step-content hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin liên hệ</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                        placeholder="Nhập email của bạn">
                    @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                        class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                        placeholder="Nhập số điện thoại">
                    @error('phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
                    <textarea name="address" rows="3" required
                        class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none resize-none"
                        placeholder="Nhập địa chỉ">{{ old('address') }}</textarea>
                    @error('address')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Step 3 -->
            <div id="step3" class="step-content hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bảo mật</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu *</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none pr-12"
                            placeholder="Nhập mật khẩu">
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2">
                        <div class="password-strength bg-gray-200 w-full" id="passwordStrength"></div>
                        <p class="text-xs text-gray-500 mt-1">Độ mạnh mật khẩu</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu *</label>
                    <div class="relative">
                        <input type="password" id="confirmPassword" name="password_confirmation" required
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none pr-12"
                            placeholder="Nhập lại mật khẩu">
                        <button type="button" onclick="togglePassword('confirmPassword')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between pt-6">
                <button type="button" id="prevBtn" onclick="prevStep()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors hidden">
                    Quay lại
                </button>
                <button type="button" id="nextBtn" onclick="nextStep()" class="register-btn px-6 py-3 text-white font-medium rounded-lg hover:shadow-lg transform transition-all duration-300 ml-auto">
                    Tiếp tục
                </button>
                <button type="submit" id="submitBtn" class="register-btn px-6 py-3 text-white font-medium rounded-lg hover:shadow-lg transform transition-all duration-300 ml-auto hidden">
                    Tạo tài khoản
                </button>
            </div>
        </form>

        <!-- Login link -->
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Đã có tài khoản? 
                <a href="{{ route('auth.login') }}" class="text-purple-600 hover:text-purple-800 font-medium">Đăng nhập ngay</a>
            </p>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function showStep(step) {
            document.querySelectorAll('.step-content').forEach((el, i) => {
                el.classList.toggle('hidden', i + 1 !== step);
            });
            
            document.querySelectorAll('.step-indicator').forEach((el, i) => {
                el.classList.remove('active', 'completed');
                if (i + 1 < step) el.classList.add('completed');
                else if (i + 1 === step) el.classList.add('active');
            });

            document.getElementById('prevBtn').classList.toggle('hidden', step === 1);
            document.getElementById('nextBtn').classList.toggle('hidden', step === totalSteps);
            document.getElementById('submitBtn').classList.toggle('hidden', step !== totalSteps);
        }

        function validateStep(step) {
            const stepEl = document.getElementById(`step${step}`);
            const fields = stepEl.querySelectorAll('input[required], select[required], textarea[required]');
            
            for (let field of fields) {
                if (!field.value.trim()) {
                    field.focus();
                    return false;
                }
                if (field.type === 'email' && !field.value.includes('@')) {
                    field.focus();
                    return false;
                }
            }

            if (step === 3) {
                const pwd = document.getElementById('password').value;
                const confirm = document.getElementById('confirmPassword').value;
                if (pwd.length < 6 || pwd !== confirm) {
                    return false;
                }
            }
            return true;
        }

        function nextStep() {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('password')?.addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;
            if (pwd.length >= 6) strength++;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) strength++;
            if (/\d/.test(pwd)) strength++;
            if (/[^a-zA-Z\d]/.test(pwd)) strength++;

            const bar = document.getElementById('passwordStrength');
            const classes = ['', 'strength-weak', 'strength-medium', 'strength-strong', 'strength-strong'];
            bar.className = `password-strength ${classes[strength]}`;
            bar.style.width = (strength * 25) + '%';
        });

        showStep(currentStep);
    </script>
</body>
</html>