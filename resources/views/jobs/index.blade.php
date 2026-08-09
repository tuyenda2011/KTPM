<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách việc làm - Công ty TNHH THT</title>
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
                <a href="{{ route('jobs.index') }}" class="text-purple-600 font-medium">Danh sách việc làm</a>
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

    <!-- Jobs List -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8">Danh sách việc làm</h1>

        @if(($supportsRecruitmentPeriods ?? false) && isset($periods) && $periods->count() > 0)
            <div class="mb-6 rounded-lg border border-purple-100 bg-white p-4">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Lọc theo kỳ tuyển dụng</h2>
                    @if($selectedPeriod)
                        <span class="text-xs px-3 py-1 rounded-full bg-purple-100 text-purple-700">
                            Đang xem: {{ $selectedPeriod->name }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('jobs.index') }}"
                        class="px-3 py-1.5 rounded-full text-sm border {{ !$selectedPeriod ? 'bg-purple-600 border-purple-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:border-purple-300 hover:text-purple-700' }}"
                    >
                        Tất cả kỳ
                    </a>

                    @foreach($periods as $period)
                        <a
                            href="{{ route('jobs.index', ['period_id' => $period->period_id]) }}"
                            class="px-3 py-1.5 rounded-full text-sm border {{ $selectedPeriod && $selectedPeriod->period_id === $period->period_id ? 'bg-purple-600 border-purple-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:border-purple-300 hover:text-purple-700' }}"
                        >
                            {{ $period->name }}
                            <span class="ml-1 text-xs opacity-80">({{ $period->active_jobs_count ?? 0 }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($jobs->count() > 0)
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-1">
                @foreach ($jobs as $job)
                    <div class="register-card rounded-lg p-6 border {{ $job->isDeadlinePassed() ? 'bg-gray-50' : '' }}">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-xl font-bold logo-text">{{ $job->title }}</h3>
                                    @if($job->isDeadlinePassed())
                                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                                            <i class="fas fa-calendar-times text-xs"></i> Hết hạn
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-600">{{ $job->department }}</p>
                                @if($supportsRecruitmentPeriods ?? false)
                                    <p class="text-sm text-purple-700 mt-1">
                                        Kỳ tuyển dụng: {{ $job->recruitmentPeriod?->name ?? 'Chưa gán kỳ' }}
                                    </p>
                                @endif
                            </div>
                            <span class="text-green-600 font-bold whitespace-nowrap ml-4">{{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }} VND</span>
                        </div>
                        <p class="text-gray-700 mb-4">{{ Str::limit($job->description, 200) }}</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm {{ $job->isDeadlinePassed() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">Hạn nộp: {{ $job->deadline?->format('d/m/Y') ?? 'N/A' }}</span>
                            <a href="{{ route('jobs.show', $job) }}" class="login-btn px-4 py-2 text-white font-medium rounded text-sm">
                                Chi tiết
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        @else
            <p class="text-gray-600">Không có việc làm nào.</p>
        @endif
    </div>
</body>
</html>