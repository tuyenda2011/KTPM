@extends('admin')
@section('title', 'Quản lý nhân viên')
@section('content')

@php
    $departmentColors = [
        'DEPT001' => 'from-pink-500 to-rose-500',
        'DEPT002' => 'from-emerald-500 to-green-500',
        'DEPT003' => 'from-blue-500 to-cyan-500',
        'DEPT004' => 'from-amber-500 to-orange-500',
    ];
@endphp

<div class="space-y-6 pt-8" style="margin-left:-27px; padding-left:5px;">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Quản lý nhân viên</h1>
            <p class="text-gray-600 mt-1">Chọn phòng ban để xem danh sách nhân viên</p>
        </div>

        @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('staff.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                + Thêm nhân viên
            </a>
        @endif
    </div>


    {{-- Button tất cả nhân viên --}}
    <div>
        <a href="{{ route('staff.list') }}"
           class="block bg-gradient-to-r from-slate-700 to-slate-900 text-white rounded-2xl shadow-lg p-6 hover:scale-[1.01] transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Tất cả nhân viên</h2>
                    <p class="text-slate-200 mt-2">Xem toàn bộ danh sách nhân viên trong công ty</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-300">Tổng số</p>
                    <p class="text-4xl font-extrabold">{{ $totalEmployees }}</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Danh sách phòng ban --}}
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Danh sách phòng ban</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            @foreach($departments as $dept)
                @php
                    $gradient = $departmentColors[$dept['department_id']] ?? 'from-indigo-500 to-violet-500';
                @endphp

                <a href="{{ route('staff.list', ['department_id' => $dept['department_id']]) }}"
                   class="block rounded-2xl shadow-lg overflow-hidden hover:scale-[1.02] transition-transform bg-white">
                    <div class="bg-gradient-to-r {{ $gradient }} p-5 text-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-xl font-bold">{{ $dept['name'] }}</h3>
                                <p class="text-sm text-white/90 mt-1">
                                    {{ $dept['description'] ?: 'Chưa có mô tả phòng ban' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-white/80">Nhân viên</p>
                                <p class="text-3xl font-extrabold">{{ $dept['employees_count'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 bg-white">
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-lg bg-green-50 p-3">
                                <p class="text-xs text-gray-500">Đang làm</p>
                                <p class="text-lg font-bold text-green-600">{{ $dept['active_count'] }}</p>
                            </div>
                            <div class="rounded-lg bg-yellow-50 p-3">
                                <p class="text-xs text-gray-500">Tạm nghỉ</p>
                                <p class="text-lg font-bold text-yellow-600">{{ $dept['on_leave_count'] }}</p>
                            </div>
                            <div class="rounded-lg bg-red-50 p-3">
                                <p class="text-xs text-gray-500">Nghỉ việc</p>
                                <p class="text-lg font-bold text-red-600">{{ $dept['resigned_count'] }}</p>
                            </div>
                        </div>

                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

@endsection