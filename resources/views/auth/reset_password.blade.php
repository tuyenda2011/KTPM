<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Công ty TNHH THT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ url('css/app.css') }}">
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center px-4 py-8">
    <div class="reset-card rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full gradient-bg flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold logo-text mb-2">Khôi phục mật khẩu</h1>
            <p id="stepDescription" class="text-gray-600">Nhập email để nhận mã xác thực</p>
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
        <form id="resetForm" method="POST" action="{{ route('auth.reset_password.submit') }}" class="space-y-6">
            @csrf

            <!-- Step 1: Email -->
            <div id="step1" class="step-content">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email đăng ký</label>
                <input type="email" id="email" name="email" required
                    class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none"
                    placeholder="Nhập email của bạn">
                <p class="text-xs text-gray-500 mt-2">Chúng tôi sẽ gửi mã xác thực vào email này</p>
                <button type="button" id="sendCodeBtn" class="reset-btn w-full py-3 px-4 text-white font-medium rounded-lg hover:shadow-lg transform transition-all duration-300 mt-4">
                    Gửi mã xác thực
                </button>
            </div>

            <!-- Step 2: OTP -->
            <div id="step2" class="step-content hidden">
                <p class="text-center text-sm text-gray-600 mb-4">Mã xác thực đã được gửi đến <span id="emailDisplay" class="font-medium"></span></p>
                <label class="block text-sm font-medium text-gray-700 mb-3 text-center">Nhập mã xác thực 6 số</label>
                <div class="flex justify-center gap-2 mb-4">
                    <input type="text" maxlength="1" class="otp-input input-field w-12 h-12 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" data-index="0">
                    <input type="text" maxlength="1" class="otp-input input-field w-12 h-12 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" data-index="1">
                    <input type="text" maxlength="1" class="otp-input input-field w-12 h-12 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" data-index="2">
                    <input type="text" maxlength="1" class="otp-input input-field w-12 h-12 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" data-index="3">
                    <input type="text" maxlength="1" class="otp-input input-field w-12 h-12 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" data-index="4">
                    <input type="text" maxlength="1" class="otp-input input-field w-12 h-12 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" data-index="5">
                </div>
                <p class="text-center text-sm text-gray-500">Mã hết hạn sau: <span id="countdown">05:00</span></p>
                <button type="button" id="verifyCodeBtn" class="reset-btn w-full py-3 px-4 text-white font-medium rounded-lg hover:shadow-lg transform transition-all duration-300 mt-4">
                    Xác thực mã
                </button>
            </div>

            <!-- Step 3: New Password -->
            <div id="step3" class="step-content hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                    <div class="relative">
                        <input type="password" id="newPassword" name="newPassword" required
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none pr-12"
                            placeholder="Nhập mật khẩu mới">
                        <button type="button" onclick="togglePassword('newPassword')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                    <div class="relative">
                        <input type="password" id="confirmNewPassword" name="confirmNewPassword" required
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none pr-12"
                            placeholder="Nhập lại mật khẩu">
                        <button type="button" onclick="togglePassword('confirmNewPassword')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="reset-btn w-full py-3 px-4 text-white font-medium rounded-lg hover:shadow-lg transform transition-all duration-300 mt-4">
                    Đặt lại mật khẩu
                </button>
            </div>
        </form>

        <!-- Back to login -->
        <div class="mt-6 text-center">
            <a href="{{ route('auth.login') }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm">
                ← Quay lại đăng nhập
            </a>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let timeLeft = 300;
        let countdownTimer;

        const stepDescriptions = {
            1: 'Nhập email để nhận mã xác thực',
            2: 'Nhập mã xác thực từ email',
            3: 'Tạo mật khẩu mới'
        };

        function showStep(step) {
            document.querySelectorAll('.step-content').forEach((el, i) => {
                el.classList.toggle('hidden', i + 1 !== step);
            });
            
            document.querySelectorAll('.step-indicator').forEach((el, i) => {
                el.classList.remove('active', 'completed');
                if (i + 1 < step) el.classList.add('completed');
                else if (i + 1 === step) el.classList.add('active');
            });

            document.getElementById('stepDescription').textContent = stepDescriptions[step];
        }

        document.getElementById('sendCodeBtn').addEventListener('click', function() {
            const email = document.getElementById('email').value;
            if (!email || !email.includes('@')) {
                alert('Vui lòng nhập email hợp lệ');
                return;
            }
            document.getElementById('emailDisplay').textContent = email;
            currentStep = 2;
            showStep(currentStep);
            startCountdown();
        });

        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, i) => {
            input.addEventListener('input', (e) => {
                if (e.target.value && i < otpInputs.length - 1) otpInputs[i + 1].focus();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && i > 0) otpInputs[i - 1].focus();
            });
        });

        document.getElementById('verifyCodeBtn').addEventListener('click', function() {
            const otp = Array.from(otpInputs).map(i => i.value).join('');
            if (otp.length !== 6) {
                alert('Vui lòng nhập đầy đủ mã xác thực');
                return;
            }
            currentStep = 3;
            showStep(currentStep);
            clearInterval(countdownTimer);
        });

        function startCountdown() {
            timeLeft = 300;
            const countdown = document.getElementById('countdown');
            countdownTimer = setInterval(() => {
                const m = Math.floor(timeLeft / 60);
                const s = timeLeft % 60;
                countdown.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                if (timeLeft <= 0) clearInterval(countdownTimer);
                timeLeft--;
            }, 1000);
        }

        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const pwd = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmNewPassword').value;
            if (pwd.length < 6 || pwd !== confirm) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự và các mật khẩu phải khớp');
            }
        });

        showStep(currentStep);
    </script>
</body>
</html>