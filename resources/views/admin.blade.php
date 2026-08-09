<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ url('css/app.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(auth()->check())
    <meta name="user-role" content="{{ auth()->user()->role }}">
    <meta name="user-id" content="{{ auth()->user()->user_id }}">
    @endif
    <style>
        .dropdown.show {
            display: block;
        }
    </style>

</head>
<body class="font-sans">
    <!-- Header Bar -->
    <header class="fixed top-0 left-0 right-0 shadow-sm px-6 py-4 z-20">

        <div class="flex items-center justify-between">
            <!-- Left side - Menu toggle and App title -->
            <div class="flex items-center space-x-4">
                <!-- Menu Toggle Button -->
                <button class="menu-toggle p-2 rounded-lg transition-colors" onclick="toggleSidebar()">
                    <div class="space-y-1">
                        <div class="menu-toggle-bar w-5 h-0.5"></div>
                        <div class="menu-toggle-bar w-5 h-0.5"></div>
                        <div class="menu-toggle-bar w-5 h-0.5"></div>
                    </div>
                </button>
                
                <div>
                    <a href="{{ route('home') }}" class="block">
                        <h2 class="header-title">Công ty TNHH THT</h2>
                    </a>
                    <a href="{{ route('home') }}" class="header-subtitle text-base font-medium hover:underline" id="pageSubtitle">Trang chủ</a>
                </div>
            </div>
            
            <!-- Right side - User -->
            <div class="user-section flex items-center space-x-4">     
                <!-- User Account Dropdown -->
                <div class="relative">
                    <div class="flex items-center space-x-3 cursor-pointer p-2 rounded-lg transition-colors" onclick="toggleDropdown()">
                        <!-- Avatar -->
                        <div class="avatar w-10 h-10 text-white rounded-full flex items-center justify-center font-bold text-lg overflow-hidden bg-blue-500">
                            @php
                                $avatarPath = Auth::user()?->avatar_path;
                            @endphp
                            @if($avatarPath)
                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="avatar" class="w-full h-full object-cover">
                            @else
                            <p>{{ Auth::user() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'A' }}</p>
                            @endif
                        </div>
                        <!-- User Name -->
                        <div class="text-right">
                            <p class="avatar-name text-sm font-bold">{{ Auth::user()->name ?? 'Người dùng' }}</p>
                            <p class="avatar-name text-xs font-semibold">
                                @php
                                    $role = Auth::user()->role ?? 'user';
                                    $roleLabel = 'Ứng viên';

                                    if ($role === 'admin') {
                                        $roleLabel = 'Quản trị viên';
                                    } elseif ($role === 'staff') {
                                        $managedDepartment = \App\Models\Department::where('manager_user_id', Auth::user()->user_id)->first();

                                        if ($managedDepartment) {
                                            $roleLabel = str_replace('Phòng ', 'Trưởng phòng ', $managedDepartment->name);
                                        } else {
                                            $roleLabel = 'Nhân viên';
                                        }
                                    }
                                @endphp
                                {{ $roleLabel }}
                            </p>
                        </div>
                        <!-- Dropdown Arrow -->
                        <svg class="dropdown-arrow w-4 h-4 transition-transform" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>

                    <!-- Dropdown Menu -->
                    <div class="dropdown absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 hidden" id="userDropdown">
                        <hr class="my-1 border-gray-100">
                        <form method="POST" action="{{ route('auth.logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" 
                                class="dropdown-logout flex items-center w-full px-4 py-2 font-medium text-red-500 hover:bg-gray-100 transition-colors">
                                <span class="mr-3">🚪</span>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar fixed top-16 left-0 w-56 h-[calc(100vh-4rem)] border-r transition-transform duration-300 ease-in-out overflow-y-auto">

        <!-- Menu Navigation -->
        <nav class="py-10">
            <a href="/dashboard" class="menu-item px-6 py-4 ">
                <span class="mr-4 text-lg">📊</span>
                <span class="text-base">Dashboard</span>
            </a>

            <a href="/staff" class="menu-item px-6 py-4">
                <span class="mr-4 text-lg">👥</span>
                <span class="text-base">Quản lý nhân viên</span>
            </a>

            <a href="/recruitment" class="menu-item px-6 py-4">
                <span class="mr-4 text-lg">🏬</span>
                <span class="text-base">Quản lý tuyển dụng</span>
            </a>
    </div>
    
        <!-- Main Content Area -->
    <main id="mainContent" class="ml-56 w-[calc(100%-14rem)] min-h-screen p-8 pt-24 transition-all">
        @yield('content')
    </main>    
    <script>
        const routes = {
            'Dashboard': '/dashboard',
            'Quản lý nhân viên': '/staff',
            'Quản lý tuyển dụng': '/recruitment',
        };

        // Highlight menu item dựa trên current URL
        function setActiveMenuItem() {
            const currentPath = window.location.pathname;
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href === currentPath) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Set active on page load
        document.addEventListener('DOMContentLoaded', setActiveMenuItem);

        // Toggle UI
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const arrow = document.getElementById('dropdownArrow');
            dropdown.classList.toggle('show');
            arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : '';
            
            // Close dropdown when clicking outside
            if (dropdown.classList.contains('show')) {
                document.addEventListener('click', function closeDropdown(e) {
                    if (!e.target.closest('.relative')) {
                        dropdown.classList.remove('show');
                        arrow.style.transform = '';
                        document.removeEventListener('click', closeDropdown);
                    }
                });
            }
        }
        
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const main = document.querySelector('#mainContent');
            const hidden = sidebar.style.transform === 'translateX(-100%)';
            sidebar.style.transform = hidden ? 'translateX(0)' : 'translateX(-100%)';
            main.style.marginLeft = hidden ? '16rem' : '0';
        }
    </script>

</html>