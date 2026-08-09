<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - Công ty TNHH THT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('css/app.css') }}">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold logo-text">Công ty TNHH THT</a>
            <div class="space-x-4">
                <a href="{{ route('jobs.index') }}" class="text-gray-600 hover:text-purple-600">Danh sách việc làm</a>
                @auth
                    <a href="{{ route('notifications.index') }}" class="relative text-gray-600 hover:text-purple-600">
                        <i class="fas fa-bell text-lg"></i>
                        @if(auth()->user()->unreadNotifications()->count() > 0)
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                {{ auth()->user()->unreadNotifications()->count() }}
                            </span>
                        @endif
                    </a>
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

    <!-- Notifications Page -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Thông báo của tôi</h1>
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.markAllAsRead') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="login-btn px-4 py-2 text-white font-medium rounded text-sm">
                        Đánh dấu tất cả đã đọc
                    </button>
                </form>
            @endif
        </div>

        @if($notifications->count() > 0)
            <div class="space-y-4">
                @foreach($notifications as $notification)
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 {{ !$notification->read_at ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                @if($notification->type === 'App\\Notifications\\JobPostingDeletedNotification')
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-exclamation-circle text-yellow-500 text-lg"></i>
                                        @if(!$notification->read_at)
                                            <span class="inline-block px-2 py-1 bg-purple-600 text-white text-xs rounded font-bold">MỚI</span>
                                        @endif
                                    </div>
                                    <h3 class="text-lg font-bold mb-2">{{ $notification->data['job_title'] ?? 'Thông báo' }}</h3>
                                    <p class="text-gray-700 mb-3">{{ $notification->data['message'] ?? '' }}</p>
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-calendar"></i>
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </p>
                                @elseif($notification->type === 'App\\Notifications\\CandidateStatusUpdatedNotification')
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-user-check text-green-600 text-lg"></i>
                                        @if(!$notification->read_at)
                                            <span class="inline-block px-2 py-1 bg-purple-600 text-white text-xs rounded font-bold">MỚI</span>
                                        @endif
                                    </div>
                                    <h3 class="text-lg font-bold mb-2">Cập nhật trạng thái hồ sơ</h3>
                                    <p class="text-gray-700 mb-2">{{ $notification->data['message'] ?? '' }}</p>
                                    @if(!empty($notification->data['source']))
                                        <p class="text-xs text-gray-500 mb-2">Nguồn cập nhật: {{ $notification->data['source'] }}</p>
                                    @endif
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-calendar"></i>
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </p>
                                @elseif($notification->type === 'App\\Notifications\\InterviewResultUpdatedNotification')
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-clipboard-check text-blue-600 text-lg"></i>
                                        @if(!$notification->read_at)
                                            <span class="inline-block px-2 py-1 bg-purple-600 text-white text-xs rounded font-bold">MỚI</span>
                                        @endif
                                    </div>
                                    <h3 class="text-lg font-bold mb-2">Cập nhật kết quả phỏng vấn</h3>
                                    <p class="text-gray-700 mb-2">{{ $notification->data['message'] ?? '' }}</p>
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-calendar"></i>
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </p>
                                @elseif($notification->type === 'App\\Notifications\\NewCandidateApplicationNotification')
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-user-plus text-indigo-600 text-lg"></i>
                                        @if(!$notification->read_at)
                                            <span class="inline-block px-2 py-1 bg-purple-600 text-white text-xs rounded font-bold">MỚI</span>
                                        @endif
                                    </div>
                                    <h3 class="text-lg font-bold mb-2">Ứng viên nộp hồ sơ mới</h3>
                                    <p class="text-gray-700 mb-2">{{ $notification->data['message'] ?? '' }}</p>
                                    @if(!empty($notification->data['applied_at']))
                                        <p class="text-xs text-gray-500 mb-2">Ngày nộp hồ sơ: {{ $notification->data['applied_at'] }}</p>
                                    @endif
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-calendar"></i>
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    <h3 class="text-lg font-bold mb-2">{{ $notification->data['message'] ?? 'Thông báo' }}</h3>
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-calendar"></i>
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex gap-2 ml-4">
                                @if(!$notification->read_at)
                                    <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="text-purple-600 hover:text-purple-800 text-lg" title="Đánh dấu đã đọc">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('notifications.delete', $notification->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-lg" title="Xóa thông báo">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-bell text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-600">Bạn không có thông báo nào</p>
            </div>
        @endif
    </div>

    <style>
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #6b7280;
        }

        .pagination a:hover {
            background-color: #f3f4f6;
        }

        .pagination .active {
            background-color: #a855f7;
            color: white;
            border-color: #a855f7;
        }
    </style>
</body>
</html>
