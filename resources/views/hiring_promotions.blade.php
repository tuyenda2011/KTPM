@extends('admin')
@section('title', 'Hoàn tất tuyển dụng')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tiếp nhận ứng viên</h1>
            <p class="text-sm text-gray-600 mt-1">Danh sách ứng viên đã đạt phỏng vấn và trở thành nhân viên chính thức.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm border" id="hiringPromotionTable">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2 text-left">Ứng viên</th>
                    <th class="px-4 py-2 text-left">Vị trí ứng tuyển</th>
                    <th class="px-4 py-2 text-left">Phỏng vấn đạt gần nhất</th>
                    <th class="px-4 py-2 text-left">Hồ sơ</th>
                    <th class="px-4 py-2 text-left">Role hiện tại</th>
                    <th class="px-4 py-2 text-left">Nâng role</th>
                </tr>
            </thead>
            <tbody>
                @forelse($candidates as $candidate)
                    @php
                        $passInterview = $candidate->interviews
                            ->where('result', 'pass')
                            ->sortByDesc('scheduled_at')
                            ->first();

                        $cvPath = null;
                        if (!empty($candidate->notes) && \Illuminate\Support\Str::startsWith($candidate->notes, 'CV: ')) {
                            $cvPath = trim(substr($candidate->notes, 4));
                        }
                        $cvUrl = $cvPath ? asset('storage/' . $cvPath) : null;
                        $isStaff = ($candidate->user?->role ?? null) === 'staff';
                    @endphp
                    <tr class="border-b hover:bg-gray-50 align-top">
                        <td class="px-4 py-2">
                            <p class="font-semibold text-gray-900">{{ $candidate->user?->name ?? '-' }}</p>
                            <p class="text-gray-600">{{ $candidate->user?->email ?? '-' }}</p>
                            <p class="text-gray-600">{{ $candidate->user?->phone ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <p>{{ $candidate->position_applied ?? '-' }}</p>
                            <p class="text-xs text-gray-500 mt-1">Job: {{ $candidate->job?->title ?? 'Không xác định' }}</p>
                        </td>
                        <td class="px-4 py-2">
                            @if($passInterview)
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded">Đã nhận việc</span>
                                <p class="text-xs text-gray-600 mt-1">{{ $passInterview->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</p>
                            @else
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Chưa có lịch đạt</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($cvUrl)
                                <a href="{{ $cvUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium">
                                    Xem CV
                                </a>
                            @else
                                <span class="text-gray-500">Không có CV</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($isStaff)
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded">Nhân viên</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded">Ứng viên</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 min-w-[280px]">
                            @if($isStaff)
                                <div class="rounded border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800">
                                    Ứng viên này đã được nâng role thành nhân viên.
                                </div>
                            @else
                                <form method="POST" action="{{ route('hiring.promote', $candidate->user_id) }}" class="space-y-2">
                                    @csrf
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Phòng ban (tùy chọn)</label>
                                        <select name="department_id" class="w-full p-2 border border-gray-300 rounded text-xs">
                                            <option value="">Chưa gán</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->department_id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Chức vụ sau tiếp nhận</label>
                                        <input
                                            type="text"
                                            name="position"
                                            value="{{ $candidate->position_applied ?? 'Nhân viên' }}"
                                            class="w-full p-2 border border-gray-300 rounded text-xs"
                                        >
                                    </div>
                                    <button
                                        type="submit"
                                        class="w-full px-3 py-2 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                                        onclick="return confirm('Xác nhận nâng role ứng viên này thành nhân viên?')"
                                    >
                                        Nâng role thành nhân viên
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Chưa có ứng viên nào đạt phỏng vấn để tiếp nhận.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $candidates->links() }}
    </div>
</div>
@endsection
