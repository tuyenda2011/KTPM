<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $job->title }} - Công ty TNHH THT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('css/app.css') }}">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold logo-text">Công ty TNHH THT</a>
            <div class="space-x-4 flex items-center">
                <a href="{{ route('jobs.index') }}" class="text-gray-600 hover:text-purple-600">Danh sách việc làm</a>
                @auth
                    <x-notification-bell />
                    <span class="text-gray-600">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('auth.logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Đăng xuất</button>
                    </form>
                @else
                    <a href="{{ route('auth.login') }}" class="text-purple-600 hover:text-purple-800">Đăng nhập</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Job Detail -->
    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="register-card rounded-lg p-8 border">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold logo-text mb-2">{{ $job->title }}</h1>
                    <p class="text-gray-600 text-lg">{{ $job->department }}</p>
                </div>
                <span class="text-green-600 font-bold text-xl">{{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }} VND</span>
            </div>

            <!-- Meta -->
            <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
                <div>
                    <p class="text-sm text-gray-600">Vị trí</p>
                    <p class="font-semibold">{{ $job->title }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Hạn nộp</p>
                    <p class="font-semibold">{{ $job->deadline?->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Địa điểm</p>
                    <p class="font-semibold">{{ $job->location ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Trạng thái</p>
                    <p class="font-semibold text-green-600">{{ $job->status }}</p>
                </div>
                @if($job->recruitmentPeriod)
                    <div>
                        <p class="text-sm text-gray-600">Kỳ tuyển dụng</p>
                        <p class="font-semibold text-purple-700">
                            {{ $job->recruitmentPeriod->name }}
                            @if(($job->recruitmentPeriod->status ?? null) === 'closed')
                                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                                    Đã đóng
                                </span>
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Description -->
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-3">Mô tả công việc</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $job->description }}</p>
            </div>

            <!-- Requirements -->
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-3">Yêu cầu</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $job->requirements }}</p>
            </div>

            <!-- Apply Form -->
            <div id="apply-section" class="bg-blue-50 rounded-lg p-6 mb-6 border border-blue-200">
                <h2 class="text-xl font-bold mb-4">Nộp Hồ Sơ</h2>

                @if($errors->any())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if($job->isDeleted())
                    <div class="mb-4 rounded border border-gray-300 bg-gray-100 p-4 text-gray-800">
                        <div class="flex items-center">
                            <i class="fas fa-ban text-gray-600 mr-3 text-lg"></i>
                            <div>
                                <p class="font-semibold">Tin tuyển dụng này đã bị xóa</p>
                                <p class="text-sm mt-1">Bạn chỉ có thể xem thông tin, không thể nộp hồ sơ.</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($isRecruitmentPeriodClosed ?? false)
                    <div class="mb-4 rounded border border-amber-200 bg-amber-50 p-4 text-amber-800">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-amber-600 mr-3 text-lg"></i>
                            <div>
                                <p class="font-semibold">Kì tuyển dụng đã kết thúc</p>
                                <p class="text-sm mt-1">Kì tuyển dụng đã kết thúc vui lòng chờ các kì tuyển dụng sắp tới.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Deadline Expired Alert -->
                @if($job->isDeadlinePassed())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 p-4 text-red-800">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3 text-lg"></i>
                            <div>
                                <p class="font-semibold">Vị trí này đã hết hạn tuyển dụng</p>
                                <p class="text-sm mt-1">Hạn nộp hồ sơ là ngày {{ $job->deadline?->format('d/m/Y') }}. Hiện tại bạn không thể nộp hồ sơ cho vị trí này.</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                        <p>{{ session('success') }}</p>
                        @if(session('uploaded_cv_url'))
                            <a
                                href="{{ session('uploaded_cv_url') }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2 inline-block font-semibold text-green-900 underline"
                            >Mở CV vừa tải lên ({{ session('uploaded_cv_name') }})</a>
                        @endif
                    </div>
                @elseif(!empty($existingApplication))
                    <div class="mb-4 rounded border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
                        <p>Bạn đã nộp hồ sơ cho vị trí này.</p>
                        <p class="mt-1">Trạng thái hiện tại: <strong>{{ $existingApplication['status'] ?? 'Đang chờ' }}</strong></p>
                        @if(!empty($existingApplication['applied_date']))
                            <p class="mt-1">Ngày nộp: {{ \Carbon\Carbon::parse($existingApplication['applied_date'])->format('d/m/Y') }}</p>
                        @endif
                        @if(!empty($existingApplication['cv_url']))
                            <a
                                href="{{ $existingApplication['cv_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2 inline-block font-semibold text-blue-900 underline"
                            >Mở CV đã nộp ({{ $existingApplication['cv_name'] }})</a>
                        @endif
                    </div>
                @endif

                @if(!($isRecruitmentPeriodClosed ?? false) && !$job->isDeadlinePassed() && !$job->isDeleted())
                    @auth
                        <form id="applyForm" method="POST" action="{{ route('jobs.apply') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->job_id }}">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ Tên</label>
                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="w-full p-2 border border-gray-300 rounded" required readonly>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ Auth::user()->email }}" class="w-full p-2 border border-gray-300 rounded" required readonly>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số Điện Thoại</label>
                                <input type="tel" name="phone" value="{{ old('phone', Auth::user()->phone ?? '') }}" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CV (PDF, DOC, DOCX)</label>
                                <input type="file" name="cv" class="w-full p-2 border border-gray-300 rounded" accept=".pdf,.doc,.docx" required>
                            </div>
                            
                            <button type="submit" class="w-full login-btn px-6 py-3 text-white font-medium rounded-lg">
                                Nộp Hồ Sơ
                            </button>
                        </form>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-yellow-800 mb-3">Vui lòng đăng nhập để nộp hồ sơ</p>
                            <a href="{{ route('auth.login') }}" class="inline-block login-btn px-6 py-2 text-white font-medium rounded-lg">
                                Đăng Nhập
                            </a>
                        </div>
                    @endauth
                @endif
            </div>

            <!-- Apply Button -->
            <a href="{{ route('jobs.index') }}" class="inline-block login-btn px-6 py-3 text-white font-medium rounded-lg mb-4">
                ← Quay lại
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shouldFocusApplySection =
                window.location.hash === '#apply-section' ||
                sessionStorage.getItem('focusApplySection') === '1' ||
                {{ $errors->any() ? 'true' : 'false' }};

            if (shouldFocusApplySection) {
                const applySection = document.getElementById('apply-section');
                if (applySection) {
                    applySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                sessionStorage.removeItem('focusApplySection');
            }

            const applyForm = document.getElementById('applyForm');
            if (applyForm) {
                applyForm.addEventListener('submit', function () {
                    sessionStorage.setItem('focusApplySection', '1');
                });
            }
        });
    </script>
</body>
</html>