<a href="{{ route('notifications.index') }}" class="relative text-gray-600 hover:text-purple-600">
    <i class="fas fa-bell text-lg"></i>
    @auth
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    @endauth
</a>
