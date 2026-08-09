@extends('admin')
@section('title', 'Quản lý tuyển dụng')
@section('content')

@php
    $periodStatusLabels = [
        'open' => 'Đang mở',
        'closed' => 'Đã đóng',
    ];

    $periodStatusClasses = [
        'open' => 'bg-green-100 text-green-700',
        'closed' => 'bg-red-100 text-red-700',
    ];

    $jobStatusLabels = [
        'active' => 'Đang tuyển',
        'filled' => 'Đã tuyển đủ',
        'closed' => 'Đã đóng',
    ];

    $activeTab = request('tab', 'jobs');
    $openPeriodForm = old('form_scope') === 'period';
@endphp

<style>
    .recruitment-shell {
        font-family: "Be Vietnam Pro", "Nunito Sans", sans-serif;
        position: relative;
        isolation: isolate;
    }

    .recruitment-shell::before {
        content: "";
        position: absolute;
        inset: -1rem -1rem auto -1rem;
        height: 220px;
        background: radial-gradient(circle at 20% 30%, rgba(14, 116, 144, 0.22), transparent 55%),
                    radial-gradient(circle at 80% 10%, rgba(14, 165, 233, 0.16), transparent 55%);
        pointer-events: none;
        z-index: -1;
    }

    .recruitment-sidebar,
    .recruitment-main {
        border: 1px solid #dbeafe;
        box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(6px);
    }

    .recruitment-sidebar {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 38%);
    }

    .recruitment-main {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .period-info-panel,
    .tab-panel {
        border-color: #dbeafe;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .tab-strip {
        border-bottom-style: dashed;
        border-bottom-color: #bfdbfe;
    }

    .recruitment-shell .tab-btn {
        border-radius: 999px 999px 0 0;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
    }

    .recruitment-shell .tab-btn:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .recruitment-shell .tab-btn.border-blue-500.text-blue-600 {
        background: linear-gradient(180deg, #eff6ff, #dbeafe);
    }

    .period-list-card,
    .period-highlight-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .period-list-card:hover,
    .period-highlight-card:hover {
        transform: translateY(-2px);
        border-color: #93c5fd;
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.15);
    }

    .recruitment-hero-card {
        position: relative;
        overflow: hidden;
    }

    .recruitment-hero-card::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -70px;
        width: 180px;
        height: 180px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.13);
    }

    .recruitment-shell input,
    .recruitment-shell select,
    .recruitment-shell textarea {
        border-color: #bfdbfe;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .recruitment-shell input:focus,
    .recruitment-shell select:focus,
    .recruitment-shell textarea:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
        outline: none;
    }

    .recruitment-shell table {
        border-color: #dbeafe;
        border-radius: 12px;
        overflow: hidden;
    }

    .recruitment-shell thead {
        background: linear-gradient(180deg, #f0f9ff, #e0f2fe);
    }

    .recruitment-shell tbody tr {
        transition: background-color 0.15s ease;
    }

    @media (max-width: 1024px) {
        .recruitment-shell {
            flex-direction: column;
            height: auto;
        }

        .recruitment-sidebar {
            width: 100%;
            max-height: 360px;
        }
    }
</style>

<div class="recruitment-shell flex gap-6 h-[calc(100vh-10rem)]" style="margin-left: -27px; padding-left: 5px;">
    <div class="recruitment-sidebar w-80 bg-white rounded-lg shadow p-4 overflow-y-auto">
        <div class="mb-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Kỳ tuyển dụng</h3>
                <button
                    type="button"
                    onclick="resetPeriodFilters()"
                    class="px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 text-sm font-medium transition-colors"
                >
                    Làm mới
                </button>
            </div>
            <p class="text-sm text-gray-600 mt-1">Chọn kỳ để quản lý danh sách tin tuyển dụng</p>
        </div>

        <input
            type="text"
            id="periodSearch"
            placeholder="Tìm kỳ tuyển dụng..."
            class="w-full p-2 mb-3 border border-gray-300 rounded"
            oninput="filterPeriods()"
        >

        <select
            id="periodStatusFilter"
            class="w-full p-2 mb-4 border border-gray-300 rounded"
            onchange="filterPeriods()"
        >
            <option value="">Tất cả trạng thái</option>
            <option value="open">Đang mở</option>
            <option value="closed">Đã đóng</option>
        </select>

        <ul id="periodListContainer" class="space-y-2">
            @forelse($periods as $period)
                @php
                    $isSelected = $selectedPeriod && $selectedPeriod->period_id === $period->period_id;
                @endphp
                <li
                    class="period-item"
                    data-name="{{ strtolower($period->name) }}"
                    data-status="{{ $period->status }}"
                >
                    <a
                        href="{{ route('recruitment.index', ['period_id' => $period->period_id, 'tab' => 'jobs']) . '#jobs-list-section' }}"
                        class="period-list-card block rounded-lg border p-3 hover:bg-gray-50 transition-colors {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $period->name }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $period->period_id }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded {{ $periodStatusClasses[$period->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $periodStatusLabels[$period->status] ?? $period->status }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="rounded bg-gray-100 py-1">
                                <p class="text-gray-500">Tổng</p>
                                <p class="font-bold text-gray-800">{{ $period->total_jobs_count }}</p>
                            </div>
                            <div class="rounded bg-green-50 py-1">
                                <p class="text-gray-500">Mở</p>
                                <p class="font-bold text-green-700">{{ $period->active_jobs_count }}</p>
                            </div>
                            <div class="rounded bg-blue-50 py-1">
                                <p class="text-gray-500">Đóng</p>
                                <p class="font-bold text-blue-700">{{ $period->closed_jobs_count + $period->filled_jobs_count }}</p>
                            </div>
                        </div>
                    </a>
                </li>
            @empty
                <li class="text-sm text-gray-500 text-center py-6">Chưa có kỳ tuyển dụng nào</li>
            @endforelse
        </ul>
    </div>

    <div class="recruitment-main flex-1 bg-white rounded-lg shadow p-6 overflow-y-auto">
        <div class="mb-6">
            <div class="flex justify-between items-start gap-4 flex-wrap">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Quản lý tuyển dụng</h1>
                    <p class="text-gray-600 mt-1">
                        {{ $selectedPeriod ? 'Quản lý chi tiết kỳ và tin tuyển dụng theo cùng một màn hình' : 'Hãy chọn một kỳ tuyển dụng ở cột trái để bắt đầu' }}
                    </p>
                </div>

                @if(auth()->check() && auth()->user()->role === 'admin' && $supportsRecruitmentPeriods)
                    <div class="flex gap-2" id="periodActionButtons">
                        <button
                            type="button"
                            onclick="startCreatePeriod()"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                        >
                            Thêm kỳ
                        </button>

                        @if($selectedPeriod)
                            <button
                                type="button"
                                data-period-id="{{ $selectedPeriod->period_id }}"
                                data-name="{{ e($selectedPeriod->name) }}"
                                data-start-date="{{ $selectedPeriod->start_date?->format('Y-m-d') }}"
                                data-end-date="{{ $selectedPeriod->end_date?->format('Y-m-d') }}"
                                data-status="{{ $selectedPeriod->status }}"
                                data-notes="{{ e($selectedPeriod->notes ?? '') }}"
                                onclick="startEditPeriodFromButton(this)"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
                            >
                                Chỉnh sửa kỳ
                            </button>

                            <form method="POST" action="{{ route('recruitment.destroyPeriod', $selectedPeriod->period_id) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa kỳ tuyển dụng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">Xóa kỳ</button>
                            </form>
                        @endif
                    </div>
                @endif
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

        @if(!$supportsRecruitmentPeriods)
            <div class="rounded border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 mb-6">
                Hệ thống chưa có cột kỳ tuyển dụng cho tin tuyển dụng. Vui lòng chạy migrate để bật giao diện mới.
            </div>
        @endif

        <div class="period-info-panel border rounded-lg p-4 bg-gray-50 mb-6">
            
            @if(auth()->check() && auth()->user()->role === 'staff')
                <div class="rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 mb-4">
                    Trưởng phòng chỉ được sử dụng kỳ tuyển dụng có sẵn do quản trị viên tạo.
                </div>
            @endif

            @if(auth()->check() && auth()->user()->role === 'admin' && $supportsRecruitmentPeriods)
                <form
                    id="periodForm"
                    method="POST"
                    action="{{ route('recruitment.storePeriod') }}"
                    data-store-action="{{ route('recruitment.storePeriod') }}"
                    data-update-action-template="{{ url('/recruitment/period/__PERIOD_ID__') }}"
                    class="space-y-4 {{ $openPeriodForm ? '' : 'hidden' }}"
                >
                    @csrf
                    <input type="hidden" name="form_scope" value="period">
                    <input type="hidden" id="period_id" value="">

                    <div class="flex items-center justify-between">
                        <h3 id="periodFormModeLabel" class="text-sm font-semibold text-gray-700">Tạo kỳ tuyển dụng mới</h3>
                        <button type="button" onclick="hidePeriodForm()" class="text-sm text-gray-600 hover:text-gray-900">Đóng form</button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên kỳ tuyển dụng</label>
                            <input id="period_name" name="name" type="text" value="{{ old('name') }}" class="w-full p-2 border border-gray-300 rounded" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="period_status" name="status" class="w-full p-2 border border-gray-300 rounded" required>
                                <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Đang mở</option>
                                <option value="closed" {{ old('status', 'closed') === 'closed' ? 'selected' : '' }}>Đã đóng</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                            <input id="period_start_date" name="start_date" type="date" value="{{ old('start_date') }}" class="w-full p-2 border border-gray-300 rounded" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                            <input id="period_end_date" name="end_date" type="date" value="{{ old('end_date') }}" class="w-full p-2 border border-gray-300 rounded" required>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="period_notes" name="notes" rows="3" class="w-full p-2 border border-gray-300 rounded">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="cancelPeriodEdit()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">Hủy</button>
                        <button id="periodSubmitBtn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Lưu kỳ tuyển dụng</button>
                    </div>
                </form>
            @else
                @if($selectedPeriod)
                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <p class="text-gray-500">Mã kỳ</p>
                            <p class="font-semibold">{{ $selectedPeriod->period_id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Trạng thái</p>
                            <p class="font-semibold">{{ $periodStatusLabels[$selectedPeriod->status] ?? $selectedPeriod->status }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Ngày bắt đầu</p>
                            <p class="font-semibold">{{ $selectedPeriod->start_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Ngày kết thúc</p>
                            <p class="font-semibold">{{ $selectedPeriod->end_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Chưa chọn kỳ tuyển dụng.</p>
                @endif
            @endif
        </div>

        <div class="tab-panel border rounded-lg p-4 bg-white">
            <div class="tab-strip mb-4 flex gap-2 border-b border-gray-200 overflow-x-auto">
                <button type="button" data-tab="jobs" class="tab-btn px-4 py-2 border-b-2 font-medium" onclick="switchTab(event, 'jobs')">Quản lý tin tuyển dụng</button>
                <button type="button" data-tab="candidates" class="tab-btn px-4 py-2 border-b-2 font-medium" onclick="switchTab(event, 'candidates')">Quản lý ứng viên</button>
                <button type="button" data-tab="interviews" class="tab-btn px-4 py-2 border-b-2 font-medium" onclick="switchTab(event, 'interviews')">Quản lý phỏng vấn</button>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <button type="button" data-tab="hiring" class="tab-btn px-4 py-2 border-b-2 font-medium" onclick="switchTab(event, 'hiring')">Hoàn tất tuyển dụng</button>
                @endif
            </div>

            <div id="jobs" class="tab-content">
                <div class="mb-6 space-y-4">
                    <a
                        href="{{ route('recruitment.index', ['tab' => 'jobs']) }}"
                        class="recruitment-hero-card block rounded-2xl bg-gradient-to-r from-slate-700 to-slate-900 text-white p-5 shadow-lg hover:scale-[1.01] transition-transform"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold">Tất cả tin tuyển dụng</h3>
                                <p class="text-slate-200 mt-1">Xem toàn bộ tin tuyển dụng trong hệ thống</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-300">Tổng số</p>
                                <p class="text-4xl font-extrabold">{{ $allJobsCount ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-center text-xs">
                            <div class="rounded bg-white/15 py-2">
                                <p class="text-slate-200">Đang tuyển</p>
                                <p class="font-bold text-white">{{ $allOpenJobsCount ?? 0 }}</p>
                            </div>
                            <div class="rounded bg-white/15 py-2">
                                <p class="text-slate-200">Đã đóng/đủ</p>
                                <p class="font-bold text-white">{{ $allClosedJobsCount ?? 0 }}</p>
                            </div>
                        </div>
                    </a>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Danh sách kỳ tuyển dụng</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @forelse($periods as $period)
                                @php
                                    $isActivePeriod = $selectedPeriod && $selectedPeriod->period_id === $period->period_id;
                                    $periodGradient = $period->status === 'open'
                                        ? 'from-emerald-500 to-green-600'
                                        : ($period->status === 'closed' ? 'from-rose-500 to-red-600' : 'from-amber-500 to-orange-600');
                                @endphp
                                <a
                                    href="{{ route('recruitment.index', ['period_id' => $period->period_id, 'tab' => 'jobs']) . '#jobs-list-section' }}"
                                    class="period-highlight-card block rounded-xl border {{ $isActivePeriod ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200' }} bg-white shadow-sm hover:shadow-md transition-shadow"
                                >
                                    <div class="bg-gradient-to-r {{ $periodGradient }} text-white rounded-t-xl p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="font-bold">{{ $period->name }}</p>
                                                <p class="text-xs text-white/90 mt-1">{{ $period->period_id }}</p>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded bg-white/20">
                                                {{ $periodStatusLabels[$period->status] ?? $period->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4 grid grid-cols-3 gap-2 text-center text-xs">
                                        <div class="rounded bg-gray-100 py-2">
                                            <p class="text-gray-500">Tổng</p>
                                            <p class="font-bold text-gray-800">{{ $period->total_jobs_count }}</p>
                                        </div>
                                        <div class="rounded bg-green-50 py-2">
                                            <p class="text-gray-500">Đang tuyển</p>
                                            <p class="font-bold text-green-700">{{ $period->active_jobs_count }}</p>
                                        </div>
                                        <div class="rounded bg-blue-50 py-2">
                                            <p class="text-gray-500">Đã đóng</p>
                                            <p class="font-bold text-blue-700">{{ $period->closed_jobs_count + $period->filled_jobs_count }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="col-span-full rounded border border-dashed border-gray-300 p-4 text-sm text-gray-500 text-center">
                                    Chưa có kỳ tuyển dụng nào.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div id="jobs-list-section" class="mb-4 flex items-center justify-between gap-4 flex-wrap">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ $selectedPeriod ? 'Danh sách tin tuyển dụng của kỳ: ' . $selectedPeriod->name : 'Danh sách tất cả tin tuyển dụng' }}
                    </h2>

                    <form method="GET" action="{{ route('recruitment.index') }}" class="flex items-center gap-2 flex-wrap">
                        @if($selectedPeriod)
                            <input type="hidden" name="period_id" value="{{ $selectedPeriod->period_id }}">
                        @endif
                        <input type="hidden" name="tab" value="jobs">
                        <input type="hidden" name="job_status" value="{{ $jobStatus }}">
                        <input
                            type="text"
                            name="job_search"
                            value="{{ $jobSearch }}"
                            placeholder="Tìm theo tiêu đề hoặc mã tin"
                            class="w-64 p-2 border border-gray-300 rounded"
                            title="Nhấn Enter để tìm"
                        >
                    </form>

                    @php
                        $jobStatusTabs = [
                            '' => 'Tất cả trạng thái',
                            'active' => 'Đang tuyển',
                            'filled' => 'Đã tuyển đủ',
                            'closed' => 'Đã đóng',
                            'deleted' => 'Đã xóa',
                        ];
                    @endphp

                    <div class="w-full flex flex-wrap items-center gap-2">
                        @foreach($jobStatusTabs as $statusKey => $statusLabel)
                            <a
                                href="{{ route('recruitment.index', array_filter([
                                    'period_id' => $selectedPeriod?->period_id,
                                    'tab' => 'jobs',
                                    'job_search' => $jobSearch !== '' ? $jobSearch : null,
                                    'job_status' => $statusKey !== '' ? $statusKey : null,
                                ])) . '#jobs-list-section' }}"
                                class="px-3 py-1.5 rounded-full text-sm border transition-colors {{ $jobStatus === $statusKey || ($statusKey === '' && $jobStatus === '') ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-blue-300 hover:text-blue-700' }}"
                            >
                                {{ $statusLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-sm border" id="jobsTable">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="px-4 py-2 text-left">Mã tin</th>
                                    <th class="px-4 py-2 text-left">Tiêu đề</th>
                                    <th class="px-4 py-2 text-left">Phòng ban</th>
                                    <th class="px-4 py-2 text-left">Địa điểm</th>
                                    <th class="px-4 py-2 text-left">Mức lương</th>
                                    <th class="px-4 py-2 text-left">Số lượng</th>
                                    <th class="px-4 py-2 text-left">Hạn nộp</th>
                                    <th class="px-4 py-2 text-left">Trạng thái</th>
                                    @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                                        <th class="px-4 py-2 text-center">Hành động</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($jobPostings ?? collect()) as $job)
                                    @php
                                        $normalizedJobStatus = match ($job->status) {
                                            'Đang tuyển' => 'active',
                                            'Đã tuyển đủ' => 'filled',
                                            'Đã đóng' => 'closed',
                                            default => $job->status,
                                        };
                                    @endphp
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-2 font-medium">{{ $job->job_id }}</td>
                                        <td class="px-4 py-2">{{ $job->title }}</td>
                                        <td class="px-4 py-2">{{ $job->department ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $job->location ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }}đ</td>
                                        <td class="px-4 py-2">{{ $job->quantity }}</td>
                                        <td class="px-4 py-2">{{ $job->deadline?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2">
                                            @if(($job->is_deleted ?? false) === true)
                                                <span class="px-2 py-1 rounded bg-gray-200 text-gray-700">Đã xóa</span>
                                            @else
                                                @php
                                                    $statusClass = $normalizedJobStatus === 'active'
                                                        ? 'bg-green-100 text-green-700'
                                                        : ($normalizedJobStatus === 'filled' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700');
                                                @endphp
                                                <span class="px-2 py-1 rounded {{ $statusClass }}">
                                                    {{ $jobStatusLabels[$normalizedJobStatus] ?? $normalizedJobStatus }}
                                                </span>
                                            @endif
                                        </td>

                                        @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                                            <td class="px-4 py-2">
                                                @php
                                                    $canManageThisJob =
                                                        auth()->user()->role === 'admin'
                                                        || (
                                                            $isDepartmentManager
                                                            && ($job->department ?? null) === $managedDepartmentName
                                                        );
                                                @endphp

                                                @if($canManageThisJob)
                                                    <div class="flex items-center justify-center gap-2 text-xs">
                                                        @if(($job->is_deleted ?? false) === false)
                                                            <button
                                                                type="button"
                                                                data-job-id="{{ $job->job_id }}"
                                                                data-period-id="{{ $job->recruitment_period_id }}"
                                                                data-title="{{ e($job->title) }}"
                                                                data-department="{{ $job->department }}"
                                                                data-location="{{ $job->location }}"
                                                                data-salary-min="{{ (int) $job->salary_min }}"
                                                                data-salary-max="{{ (int) $job->salary_max }}"
                                                                data-quantity="{{ $job->quantity }}"
                                                                data-deadline="{{ $job->deadline?->format('Y-m-d') }}"
                                                                data-description="{{ e($job->description ?? '') }}"
                                                                data-requirements="{{ e($job->requirements ?? '') }}"
                                                                data-status="{{ $normalizedJobStatus }}"
                                                                onclick="startEditJobFromButton(this)"
                                                                class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                                                            >Chỉnh sửa</button>

                                                            <form method="POST" action="{{ route('recruitment.destroyJob', ['jobId' => $job->job_id]) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa tin tuyển dụng này?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                                                                <input type="hidden" name="tab" value="jobs" class="js-current-tab-input">
                                                                <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Xóa</button>
                                                            </form>
                                                        @else
                                                            <span class="text-gray-500">Chỉ xem</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager) ? 9 : 8 }}" class="px-4 py-6 text-center text-gray-500">
                                            Chưa có tin tuyển dụng phù hợp bộ lọc.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($jobPostings && method_exists($jobPostings, 'links'))
                        <div class="mb-6">
                            {{ $jobPostings->appends(request()->except('jobs_page'))->links() }}
                        </div>
                    @endif

                    @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                        <form
                            id="jobForm"
                            method="POST"
                            action="{{ route('recruitment.storeJob') }}"
                            data-store-action="{{ route('recruitment.storeJob') }}"
                            data-update-action-template="{{ url('/recruitment/job/__JOB_ID__') }}"
                            class="border-t pt-6 space-y-4"
                        >
                            @csrf
                            <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                            <input type="hidden" name="tab" value="jobs" class="js-current-tab-input">
                            <input type="hidden" id="job_id" value="">

                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Thêm hoặc cập nhật tin tuyển dụng</h3>
                                <button type="button" onclick="startCreateJob()" class="px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Mẫu mới</button>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kỳ tuyển dụng</label>
                                    <select id="job_recruitment_period_id" name="recruitment_period_id" data-default-value="{{ $selectedPeriod?->period_id ?? '' }}" class="w-full p-2 border border-gray-300 rounded" required>
                                        <option value="">Chọn kỳ tuyển dụng</option>
                                        @foreach($periods as $period)
                                            <option value="{{ $period->period_id }}" {{ $selectedPeriod && $selectedPeriod->period_id === $period->period_id ? 'selected' : '' }}>
                                                {{ $period->name }} ({{ $period->period_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                                    <input id="job_title" name="title" type="text" class="w-full p-2 border border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Phòng ban</label>

                                    @if(auth()->user()->role === 'admin')
                                        <select name="department" id="department" class="w-full p-2 border border-gray-300 rounded" required>
                                            <option value="">-- Chọn phòng ban --</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->name }}">
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="w-full p-2 border border-gray-300 rounded bg-gray-100" value="{{ $managedDepartmentName }}" readonly>
                                        <input type="hidden" name="department" value="{{ $managedDepartmentName }}">
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                    <select id="job_status" name="status" class="w-full p-2 border border-gray-300 rounded" required>
                                        <option value="active">Đang tuyển</option>
                                        <option value="filled">Đã tuyển đủ</option>
                                        <option value="closed">Đã đóng</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Địa điểm</label>
                                    <input id="job_location" name="location" type="text" class="w-full p-2 border border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mức lương từ</label>
                                    <input id="job_salary_min" name="salary_min" type="number" min="0" class="w-full p-2 border border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mức lương đến</label>
                                    <input id="job_salary_max" name="salary_max" type="number" min="0" class="w-full p-2 border border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng tuyển</label>
                                    <input id="job_quantity" name="quantity" type="number" min="1" class="w-full p-2 border border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hạn nộp</label>
                                    <input id="job_deadline" name="deadline" type="date" class="w-full p-2 border border-gray-300 rounded" required>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                                    <textarea id="job_description" name="description" rows="4" class="w-full p-2 border border-gray-300 rounded" required></textarea>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Yêu cầu</label>
                                    <textarea id="job_requirements" name="requirements" rows="4" class="w-full p-2 border border-gray-300 rounded" required></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="cancelJobEdit()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">Hủy</button>
                                <button id="jobSubmitBtn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Lưu tin tuyển dụng</button>
                            </div>
                        </form>
                    @endif
            </div>

            <div id="candidates" class="tab-content hidden">
                <div class="mb-4 flex items-center gap-2 flex-wrap">
                    <input
                        id="candidateSearchInput"
                        type="text"
                        placeholder="Tìm ứng viên theo tên, email, SĐT, vị trí..."
                        class="w-80 p-2 border border-gray-300 rounded"
                    >
                    <select id="candidateStatusFilter" class="p-2 border border-gray-300 rounded" onchange="applyCandidateFilters()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Đang chờ">Đang chờ</option>
                        <option value="Đã duyệt CV">Đã duyệt CV</option>
                        <option value="Phỏng vấn">Phỏng vấn</option>
                        <option value="Đã nhận việc">Đã nhận việc</option>
                        <option value="Từ chối">Từ chối</option>
                        <option value="Đã xóa">Đã xóa</option>
                    </select>
                    <button type="button" onclick="applyCandidateFilters()" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Lọc</button>
                    <button type="button" onclick="resetCandidateFilters()" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Làm mới</button>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-sm border" id="candidatesTable">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-2 text-left">Họ tên</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">SĐT</th>
                                <th class="px-4 py-2 text-left">Vị trí</th>
                                <th class="px-4 py-2 text-left">Phòng ban</th>
                                <th class="px-4 py-2 text-left">CV</th>
                                <th class="px-4 py-2 text-left">Trạng thái</th>
                                @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                                    <th class="px-4 py-2 text-center">Hành động</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidates as $candidate)
                                @php
                                    $cvPath = null;
                                    if (!empty($candidate->notes) && preg_match('/(?:^|\r\n|\r|\n)CV:\s*([^\r\n]+)/u', $candidate->notes, $matches)) {
                                        $cvPath = trim($matches[1]);
                                    }
                                    $cvUrl = $cvPath ? route('recruitment.candidateCv', $candidate->user_id) : null;
                                    $isCandidateJobDeleted = ($candidate->job?->is_deleted ?? false) === true;
                                    $candidateDisplayStatus = $isCandidateJobDeleted ? 'Đã xóa' : $candidate->status;
                                    $candidateSearchBlob = \Illuminate\Support\Str::lower(trim(implode(' ', [
                                        $candidate->user?->name ?? '',
                                        $candidate->user?->email ?? '',
                                        $candidate->user?->phone ?? '',
                                        $candidate->position_applied ?? '',
                                        $candidate->job?->department ?? '',
                                    ])));
                                @endphp
                                <tr
                                    class="border-b hover:bg-gray-50"
                                    data-row-type="candidate"
                                    data-candidate-search="{{ $candidateSearchBlob }}"
                                    data-candidate-status="{{ \Illuminate\Support\Str::lower($candidateDisplayStatus) }}"
                                >
                                    <td class="px-4 py-2">{{ $candidate->user?->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $candidate->user?->email ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $candidate->user?->phone ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $candidate->position_applied ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $candidate->job?->department ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @if($cvUrl)
                                            <a href="{{ $cvUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Xem CV</a>
                                        @else
                                            <span class="text-gray-500">Không có CV</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($isCandidateJobDeleted)
                                            <span class="px-2 py-1 rounded bg-gray-200 text-gray-700">Đã xóa</span>
                                        @else
                                            {{ $candidate->status }}
                                        @endif
                                    </td>
                                    @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                                        <td class="px-4 py-2">
                                            @php
                                                $canManageThisCandidate =
                                                    auth()->user()->role === 'admin'
                                                    || (
                                                        $isDepartmentManager
                                                        && $candidate->job
                                                        && $candidate->job->department === $managedDepartmentName
                                                    );
                                            @endphp

                                            @if($canManageThisCandidate && !$isCandidateJobDeleted)
                                                <div class="flex items-center justify-center gap-2 text-xs">
                                                    <button
                                                        type="button"
                                                        data-user-id="{{ $candidate->user_id }}"
                                                        data-name="{{ $candidate->user?->name }}"
                                                        data-email="{{ $candidate->user?->email }}"
                                                        data-phone="{{ $candidate->user?->phone }}"
                                                        data-position="{{ $candidate->position_applied }}"
                                                        data-status="{{ $candidateDisplayStatus }}"
                                                        data-job-id="{{ $candidate->job_id }}"
                                                        data-cv-url="{{ $cvUrl }}"
                                                        data-cv-name="{{ $cvPath ? basename($cvPath) : '' }}"
                                                        onclick="editCandidateFromButton(this)"
                                                        class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                                                    >Chỉnh sửa</button>

                                                    @if(auth()->user()->role === 'admin')
                                                        <form method="POST" action="{{ route('recruitment.destroyCandidate', $candidate->user_id) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa ứng viên?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                                                            <input type="hidden" name="tab" value="candidates" class="js-current-tab-input">
                                                            <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Xóa</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">{{ $isCandidateJobDeleted ? 'Đã xóa' : '-' }}</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager) ? 8 : 7 }}" class="px-4 py-6 text-center text-gray-500">Chưa có ứng viên</td>
                                </tr>
                            @endforelse
                            <tr id="candidateNoResultRow" class="hidden">
                                <td colspan="{{ auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager) ? 8 : 7 }}" class="px-4 py-4 text-center text-gray-500">
                                    Không tìm thấy ứng viên phù hợp bộ lọc.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                    <form id="candidateForm" method="POST" action="{{ route('recruitment.storeCandidate') }}" enctype="multipart/form-data" class="border-t pt-6 space-y-4">
                        @csrf
                        <input type="hidden" id="candidate_user_id">
                        <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                        <input type="hidden" name="tab" value="candidates" class="js-current-tab-input">
                        <h3 class="text-lg font-semibold text-gray-900">Thêm hoặc cập nhật ứng viên</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vị trí ứng tuyển</label>
                                <select id="candidate_job_id" name="job_id" class="w-full p-2 border border-gray-300 rounded">
                                    <option value="">Không gán</option>
                                    @foreach(($activeJobPostings ?? collect()) as $job)
                                        <option value="{{ $job->job_id }}">{{ $job->title }} ({{ $job->job_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                                <input id="candidate_name" name="name" type="text" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vị trí</label>
                                <input id="candidate_position" name="position" type="text" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input id="candidate_email" name="email" type="email" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SĐT</label>
                                <input id="candidate_phone" name="phone" type="text" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                <select id="candidate_status" name="status" class="w-full p-2 border border-gray-300 rounded" required>
                                    <option>Đang chờ</option>
                                    <option>Đã duyệt CV</option>
                                    <option>Phỏng vấn</option>
                                    <option>Đã nhận việc</option>
                                    <option>Từ chối</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CV</label>
                                <input id="candidate_cv" name="cv_path" type="file" class="w-full p-2 border border-gray-300 rounded">
                                <p id="candidate_existing_cv_wrapper" class="mt-2 text-sm text-gray-600 hidden">
                                    CV hiện tại:
                                    <a id="candidate_existing_cv_link" href="#" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline"></a>
                                </p>
                            </div>
                            <div class="col-span-2 flex justify-end gap-2">
                                <button type="button" onclick="resetCandidateForm()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">Hủy</button>
                                <button id="candidateSubmitBtn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Lưu ứng viên</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            <div id="interviews" class="tab-content hidden">
                <div class="mb-4 flex items-center gap-2 flex-wrap">
                    <input
                        id="interviewSearchInput"
                        type="text"
                        placeholder="Tìm theo ứng viên, email, SĐT, phòng ban..."
                        class="w-80 p-2 border border-gray-300 rounded"
                    >
                    <select id="interviewStatusFilter" class="p-2 border border-gray-300 rounded" onchange="applyInterviewFilters()">
                        <option value="">Tất cả kết quả</option>
                        <option value="Chờ kết quả">Chờ kết quả</option>
                        <option value="Đã nhận việc">Đã nhận việc</option>
                        <option value="Từ chối">Từ chối</option>
                    </select>
                    <button type="button" onclick="applyInterviewFilters()" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Lọc</button>
                    <button type="button" onclick="resetInterviewFilters()" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Làm mới</button>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-sm border" id="interviewsTable">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-2 text-left">Tên</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">SĐT</th>
                                <th class="px-4 py-2 text-left">Phòng ban</th>
                                <th class="px-4 py-2 text-left">Ngày và giờ</th>
                                <th class="px-4 py-2 text-left">Kết quả</th>
                                <th class="px-4 py-2 text-left">Ghi chú</th>
                                @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                                    <th class="px-4 py-2 text-center">Hành động</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($interviews as $interview)
                                @php
                                    $interviewResultRaw = trim((string) $interview->result);
                                    $interviewResultLabel = match ($interviewResultRaw) {
                                        'pass', 'Đã nhận việc', 'Nhận việc', 'Đậu' => 'Đã nhận việc',
                                        'fail', 'Từ chối', 'Rớt' => 'Từ chối',
                                        default => 'Chờ kết quả',
                                    };
                                    $interviewResultClass = $interviewResultLabel === 'Đã nhận việc'
                                        ? 'bg-green-100 text-green-700'
                                        : ($interviewResultLabel === 'Từ chối' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700');
                                    $interviewSearchBlob = \Illuminate\Support\Str::lower(trim(implode(' ', [
                                        $interview->candidate?->user?->name ?? '',
                                        $interview->candidate?->user?->email ?? '',
                                        $interview->candidate?->user?->phone ?? '',
                                        $interview->candidate?->job?->department ?? '',
                                        $interview->notes ?? '',
                                    ])));
                                @endphp
                                <tr
                                    class="border-b hover:bg-gray-50"
                                    data-row-type="interview"
                                    data-interview-search="{{ $interviewSearchBlob }}"
                                    data-interview-status="{{ \Illuminate\Support\Str::lower($interviewResultLabel) }}"
                                >
                                    <td class="px-4 py-2">{{ $interview->candidate?->user?->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $interview->candidate?->user?->email ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $interview->candidate?->user?->phone ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $interview->candidate?->job?->department ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $interview->scheduled_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded {{ $interviewResultClass }}">{{ $interviewResultLabel }}</span>
                                    </td>
                                    <td class="px-4 py-2">{{ $interview->notes ?? '-' }}</td>
                                    @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                                        <td class="px-4 py-2">
                                            @php
                                                $canManageThisInterview =
                                                    auth()->user()->role === 'admin'
                                                    || (
                                                        $isDepartmentManager
                                                        && $interview->candidate
                                                        && $interview->candidate->job
                                                        && ($interview->candidate->job->department ?? null) === $managedDepartmentName
                                                    );
                                            @endphp

                                            @if($canManageThisInterview)
                                                <div class="flex items-center justify-center gap-2 text-xs">
                                                    <button
                                                        type="button"
                                                        data-interview-id="{{ $interview->interview_id }}"
                                                        data-name="{{ $interview->candidate?->user?->name }}"
                                                        data-email="{{ $interview->candidate?->user?->email }}"
                                                        data-phone="{{ $interview->candidate?->user?->phone }}"
                                                        data-date="{{ $interview->scheduled_at?->format('Y-m-d') }}"
                                                        data-time="{{ $interview->scheduled_at?->format('H:i') }}"
                                                        data-result="{{ $interviewResultLabel }}"
                                                        data-notes="{{ $interview->notes }}"
                                                        onclick="editInterviewFromButton(this)"
                                                        class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                                                    >Chỉnh sửa</button>

                                                    <form method="POST" action="{{ route('recruitment.destroyInterview', $interview->interview_id) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa lịch phỏng vấn?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                                                        <input type="hidden" name="tab" value="interviews" class="js-current-tab-input">
                                                        <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Xóa</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager) ? 8 : 7 }}" class="px-4 py-6 text-center text-gray-500">Chưa có lịch phỏng vấn</td>
                                </tr>
                            @endforelse
                            <tr id="interviewNoResultRow" class="hidden">
                                <td colspan="{{ auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager) ? 8 : 7 }}" class="px-4 py-4 text-center text-gray-500">
                                    Không tìm thấy lịch phỏng vấn phù hợp bộ lọc.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if(auth()->check() && (auth()->user()->role === 'admin' || $isDepartmentManager))
                    <form id="interviewForm" method="POST" action="{{ route('recruitment.storeInterview') }}" class="border-t pt-6 space-y-4">
                        @csrf
                        <input type="hidden" id="interview_id">
                        <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                        <input type="hidden" name="tab" value="interviews" class="js-current-tab-input">
                        <h3 class="text-lg font-semibold text-gray-900">Thêm hoặc cập nhật lịch phỏng vấn</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên ứng viên</label>
                                <input id="interview_candidate_name" type="text" class="w-full p-2 border border-gray-300 rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input id="interview_candidate_email" name="candidate_email" type="email" class="w-full p-2 border border-gray-300 rounded" required list="candidateEmails">
                                <datalist id="candidateEmails">
                                    @foreach($interviewCandidates as $candidate)
                                        <option value="{{ $candidate->user?->email }}" data-name="{{ $candidate->user?->name }}" data-phone="{{ $candidate->user?->phone }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SĐT</label>
                                <input id="interview_candidate_phone" type="text" class="w-full p-2 border border-gray-300 rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày phỏng vấn</label>
                                <input id="interview_date" name="interview_date" type="date" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giờ phỏng vấn</label>
                                <input id="interview_time" name="interview_time" type="time" class="w-full p-2 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kết quả</label>
                                <select id="interview_result" name="result" class="w-full p-2 border border-gray-300 rounded" required>
                                    <option>Chờ kết quả</option>
                                    <option>Đã nhận việc</option>
                                    <option>Từ chối</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                <textarea id="interview_notes" name="notes" class="w-full p-2 border border-gray-300 rounded" rows="4"></textarea>
                            </div>
                            <div class="col-span-2 flex justify-end gap-2">
                                <button type="button" onclick="resetInterviewForm()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">Hủy</button>
                                <button id="interviewSubmitBtn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Lưu lịch phỏng vấn</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            <div id="hiring" class="tab-content hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border" id="hiringTable">
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
                            @forelse($hiringCandidates as $candidate)
                                @php
                                    $passInterview = $candidate->interviews->where('result', 'pass')->sortByDesc('scheduled_at')->first();
                                    $cvPath = null;
                                    if (!empty($candidate->notes) && preg_match('/(?:^|\r\n|\r|\n)CV:\s*([^\r\n]+)/u', $candidate->notes, $matches)) {
                                        $cvPath = trim($matches[1]);
                                    }
                                    $cvUrl = $cvPath ? route('recruitment.candidateCv', $candidate->user_id) : null;
                                    $isStaff = ($candidate->user?->role ?? null) === 'staff';
                                @endphp
                                <tr class="border-b hover:bg-gray-50 align-top">
                                    <td class="px-4 py-2">
                                        <p class="font-semibold text-gray-900">{{ $candidate->user?->name ?? '-' }}</p>
                                        <p class="text-gray-600">{{ $candidate->user?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-2">{{ $candidate->position_applied ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $passInterview?->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @if($cvUrl)
                                            <a href="{{ $cvUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Xem CV</a>
                                        @else
                                            <span class="text-gray-500">Không có CV</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $isStaff ? 'Nhân viên' : 'Ứng viên' }}</td>
                                    <td class="px-4 py-2 min-w-[260px]">
                                        @if(auth()->check() && auth()->user()->role === 'admin')
                                            @if($isStaff)
                                                <span class="text-xs text-blue-700">Đã nâng role thành nhân viên.</span>
                                            @else
                                                <form method="POST" action="{{ route('hiring.promote', $candidate->user_id) }}" class="space-y-2">
                                                    @csrf
                                                    <input type="hidden" name="period_id" value="{{ $selectedPeriod?->period_id }}">
                                                    <input type="hidden" name="tab" value="hiring" class="js-current-tab-input">
                                                    <select name="department_id" class="w-full p-2 border border-gray-300 rounded text-xs">
                                                        <option value="">Chưa gán phòng ban</option>
                                                        @foreach(($departments ?? collect()) as $department)
                                                            <option value="{{ $department->department_id }}">{{ $department->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="position" value="{{ $candidate->position_applied ?? 'Nhân viên' }}" class="w-full p-2 border border-gray-300 rounded text-xs">
                                                    <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white text-xs rounded hover:bg-blue-700" onclick="return confirm('Xác nhận nâng role ứng viên này thành nhân viên?')">Nâng role thành nhân viên</button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-500">Chỉ quản trị viên có quyền nâng role.</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Chưa có ứng viên đạt phỏng vấn để tiếp nhận.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const TAB_STORAGE_KEY = 'recruitment_active_tab';

function normalizeText(value) {
    return (value || '').toString().trim().toLowerCase();
}

function activateTab(tabName) {
    document.querySelectorAll('.tab-content').forEach((tab) => tab.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach((btn) => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-600');
    });

    const tabElement = document.getElementById(tabName);
    if (tabElement) {
        tabElement.classList.remove('hidden');
    }

    const activeButton = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('border-blue-500', 'text-blue-600');
        activeButton.classList.remove('border-transparent', 'text-gray-600');
    }

    document.querySelectorAll('.js-current-tab-input').forEach((input) => {
        input.value = tabName;
    });
}

function switchTab(event, tabName) {
    activateTab(tabName);
    localStorage.setItem(TAB_STORAGE_KEY, tabName);
}

function filterPeriods() {
    const keyword = normalizeText(document.getElementById('periodSearch')?.value);
    const status = document.getElementById('periodStatusFilter')?.value || '';
    const items = Array.from(document.querySelectorAll('.period-item'));

    items.forEach((item) => {
        const name = item.dataset.name || '';
        const currentStatus = item.dataset.status || '';
        const matchKeyword = !keyword || name.includes(keyword);
        const matchStatus = !status || currentStatus === status;
        item.style.display = matchKeyword && matchStatus ? '' : 'none';
    });
}

function resetPeriodFilters() {
    const search = document.getElementById('periodSearch');
    const status = document.getElementById('periodStatusFilter');
    if (search) search.value = '';
    if (status) status.selectedIndex = 0;
    filterPeriods();
}

function setPeriodFormVisibility(visible) {
    const form = document.getElementById('periodForm');
    const placeholder = document.getElementById('periodFormPlaceholder');
    if (!form) {
        return;
    }

    form.classList.toggle('hidden', !visible);
    if (placeholder) {
        placeholder.classList.toggle('hidden', visible);
    }
}

function resetPeriodFormFields() {
    const form = document.getElementById('periodForm');
    if (!form) {
        return;
    }

    form.action = form.dataset.storeAction;
    document.getElementById('period_id').value = '';
    document.getElementById('period_name').value = '';
    document.getElementById('period_start_date').value = '';
    document.getElementById('period_end_date').value = '';
    document.getElementById('period_status').value = 'closed';
    document.getElementById('period_notes').value = '';
    document.getElementById('periodSubmitBtn').textContent = 'Lưu kỳ tuyển dụng';
    const modeLabel = document.getElementById('periodFormModeLabel');
    if (modeLabel) {
        modeLabel.textContent = 'Tạo kỳ tuyển dụng mới';
    }
}

function hidePeriodForm() {
    resetPeriodFormFields();
    setPeriodFormVisibility(false);
}

function startCreatePeriod() {
    const form = document.getElementById('periodForm');
    if (!form) {
        return;
    }

    resetPeriodFormFields();
    setPeriodFormVisibility(true);
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function startEditPeriodFromButton(button) {
    const form = document.getElementById('periodForm');
    if (!form) {
        return;
    }

    setPeriodFormVisibility(true);
    form.action = form.dataset.updateActionTemplate.replace('__PERIOD_ID__', button.dataset.periodId);
    document.getElementById('period_id').value = button.dataset.periodId || '';
    document.getElementById('period_name').value = button.dataset.name || '';
    document.getElementById('period_start_date').value = button.dataset.startDate || '';
    document.getElementById('period_end_date').value = button.dataset.endDate || '';
    document.getElementById('period_status').value = button.dataset.status || 'closed';
    document.getElementById('period_notes').value = button.dataset.notes || '';
    document.getElementById('periodSubmitBtn').textContent = 'Cập nhật kỳ tuyển dụng';
    const modeLabel = document.getElementById('periodFormModeLabel');
    if (modeLabel) {
        modeLabel.textContent = 'Cập nhật kỳ tuyển dụng';
    }
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelPeriodEdit() {
    hidePeriodForm();
}

function startCreateJob() {
    const form = document.getElementById('jobForm');
    if (!form) {
        return;
    }

    const periodSelect = document.getElementById('job_recruitment_period_id');

    form.action = form.dataset.storeAction;
    document.getElementById('job_id').value = '';
    if (periodSelect) {
        periodSelect.value = periodSelect.dataset.defaultValue || '';
    }

    const departmentField = document.getElementById('department');
    if (departmentField && departmentField.tagName === 'SELECT') {
        departmentField.value = '';
    }

    document.getElementById('job_title').value = '';
    document.getElementById('job_location').value = '';
    document.getElementById('job_salary_min').value = '';
    document.getElementById('job_salary_max').value = '';
    document.getElementById('job_quantity').value = '';
    document.getElementById('job_deadline').value = '';
    document.getElementById('job_status').value = 'active';
    document.getElementById('job_description').value = '';
    document.getElementById('job_requirements').value = '';
    document.getElementById('jobSubmitBtn').textContent = 'Lưu tin tuyển dụng';
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function startEditJobFromButton(button) {
    const form = document.getElementById('jobForm');
    if (!form) {
        return;
    }

    const periodSelect = document.getElementById('job_recruitment_period_id');

    form.action = form.dataset.updateActionTemplate.replace('__JOB_ID__', button.dataset.jobId);
    document.getElementById('job_id').value = button.dataset.jobId || '';
    if (periodSelect) {
        periodSelect.value = button.dataset.periodId || periodSelect.dataset.defaultValue || '';
    }

    const departmentField = document.getElementById('department');
    if (departmentField && departmentField.tagName === 'SELECT') {
        departmentField.value = button.dataset.department || '';
    }

    document.getElementById('job_title').value = button.dataset.title || '';
    document.getElementById('job_location').value = button.dataset.location || '';
    document.getElementById('job_salary_min').value = button.dataset.salaryMin || '';
    document.getElementById('job_salary_max').value = button.dataset.salaryMax || '';
    document.getElementById('job_quantity').value = button.dataset.quantity || '';
    document.getElementById('job_deadline').value = button.dataset.deadline || '';
    document.getElementById('job_status').value = button.dataset.status || 'active';
    document.getElementById('job_description').value = button.dataset.description || '';
    document.getElementById('job_requirements').value = button.dataset.requirements || '';
    document.getElementById('jobSubmitBtn').textContent = 'Cập nhật tin tuyển dụng';
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelJobEdit() {
    startCreateJob();
}

function applyCandidateFilters() {
    const keyword = normalizeText(document.getElementById('candidateSearchInput')?.value);
    const status = normalizeText(document.getElementById('candidateStatusFilter')?.value);
    const rows = Array.from(document.querySelectorAll('#candidatesTable tbody tr[data-row-type="candidate"]'));
    const noResultRow = document.getElementById('candidateNoResultRow');

    if (!rows.length) {
        return;
    }

    let visibleCount = 0;

    rows.forEach((row) => {
        const searchBlob = row.dataset.candidateSearch || '';
        const rowStatus = normalizeText(row.dataset.candidateStatus || '');
        const matchKeyword = !keyword || searchBlob.includes(keyword);
        const matchStatus = !status || rowStatus === status;
        const isVisible = matchKeyword && matchStatus;

        row.style.display = isVisible ? '' : 'none';
        if (isVisible) {
            visibleCount++;
        }
    });

    if (noResultRow) {
        noResultRow.classList.toggle('hidden', visibleCount > 0);
    }
}

function resetCandidateFilters() {
    const searchInput = document.getElementById('candidateSearchInput');
    const statusSelect = document.getElementById('candidateStatusFilter');

    if (searchInput) {
        searchInput.value = '';
    }

    if (statusSelect) {
        statusSelect.value = '';
    }

    applyCandidateFilters();
}

function applyInterviewFilters() {
    const keyword = normalizeText(document.getElementById('interviewSearchInput')?.value);
    const status = normalizeText(document.getElementById('interviewStatusFilter')?.value);
    const rows = Array.from(document.querySelectorAll('#interviewsTable tbody tr[data-row-type="interview"]'));
    const noResultRow = document.getElementById('interviewNoResultRow');

    if (!rows.length) {
        return;
    }

    let visibleCount = 0;

    rows.forEach((row) => {
        const searchBlob = row.dataset.interviewSearch || '';
        const rowStatus = normalizeText(row.dataset.interviewStatus || '');
        const matchKeyword = !keyword || searchBlob.includes(keyword);
        const matchStatus = !status || rowStatus === status;
        const isVisible = matchKeyword && matchStatus;

        row.style.display = isVisible ? '' : 'none';
        if (isVisible) {
            visibleCount++;
        }
    });

    if (noResultRow) {
        noResultRow.classList.toggle('hidden', visibleCount > 0);
    }
}

function resetInterviewFilters() {
    const searchInput = document.getElementById('interviewSearchInput');
    const statusSelect = document.getElementById('interviewStatusFilter');

    if (searchInput) {
        searchInput.value = '';
    }

    if (statusSelect) {
        statusSelect.value = '';
    }

    applyInterviewFilters();
}

function resetCandidateForm() {
    const form = document.getElementById('candidateForm');
    if (!form) {
        return;
    }

    const isManager = @json($isDepartmentManager ?? false);
    const isAdmin = @json(auth()->check() && auth()->user()->role === 'admin');

    form.action = isAdmin ? "{{ route('recruitment.storeCandidate') }}" : "#";
    document.getElementById('candidate_user_id').value = '';
    document.getElementById('candidate_name').value = '';
    document.getElementById('candidate_position').value = '';
    document.getElementById('candidate_email').value = '';
    document.getElementById('candidate_phone').value = '';
    document.getElementById('candidate_job_id').selectedIndex = 0;
    document.getElementById('candidate_status').selectedIndex = 0;
    document.getElementById('candidate_cv').value = '';

    const wrapper = document.getElementById('candidate_existing_cv_wrapper');
    const link = document.getElementById('candidate_existing_cv_link');
    link.href = '#';
    link.textContent = '';
    wrapper.classList.add('hidden');

    const submitBtn = document.getElementById('candidateSubmitBtn');

    if (isAdmin) {
        submitBtn.textContent = 'Lưu ứng viên';
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else if (isManager) {
        submitBtn.textContent = 'Chọn ứng viên để cập nhật';
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function editCandidateFromButton(button) {
    const form = document.getElementById('candidateForm');
    if (!form) {
        return;
    }

    form.action = `{{ url('/recruitment/candidate') }}/${button.dataset.userId}`;
    document.getElementById('candidate_user_id').value = button.dataset.userId ?? '';
    document.getElementById('candidate_name').value = button.dataset.name ?? '';
    document.getElementById('candidate_email').value = button.dataset.email ?? '';
    document.getElementById('candidate_phone').value = button.dataset.phone ?? '';
    document.getElementById('candidate_job_id').value = button.dataset.jobId ?? '';
    document.getElementById('candidate_position').value = button.dataset.position ?? '';
    document.getElementById('candidate_status').value = button.dataset.status ?? 'Đang chờ';

    const wrapper = document.getElementById('candidate_existing_cv_wrapper');
    const link = document.getElementById('candidate_existing_cv_link');
    if (button.dataset.cvUrl) {
        link.href = button.dataset.cvUrl;
        link.textContent = button.dataset.cvName || 'Xem CV đã tải';
        wrapper.classList.remove('hidden');
    } else {
        link.href = '#';
        link.textContent = '';
        wrapper.classList.add('hidden');
    }

    const submitBtn = document.getElementById('candidateSubmitBtn');
    submitBtn.textContent = 'Cập nhật ứng viên';
    submitBtn.disabled = false;
    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetInterviewForm() {
    const form = document.getElementById('interviewForm');
    if (!form) {
        return;
    }

    form.action = "{{ route('recruitment.storeInterview') }}";
    document.getElementById('interview_id').value = '';
    document.getElementById('interview_candidate_name').value = '';
    document.getElementById('interview_candidate_email').value = '';
    document.getElementById('interview_candidate_phone').value = '';
    document.getElementById('interview_date').value = '';
    document.getElementById('interview_time').value = '';
    document.getElementById('interview_result').selectedIndex = 0;
    document.getElementById('interview_notes').value = '';
    document.getElementById('interviewSubmitBtn').textContent = 'Lưu lịch phỏng vấn';
}

function editInterviewFromButton(button) {
    const form = document.getElementById('interviewForm');
    if (!form) {
        return;
    }

    form.action = `{{ url('/recruitment/interview') }}/${button.dataset.interviewId}`;
    document.getElementById('interview_id').value = button.dataset.interviewId ?? '';
    document.getElementById('interview_candidate_name').value = button.dataset.name ?? '';
    document.getElementById('interview_candidate_email').value = button.dataset.email ?? '';
    document.getElementById('interview_candidate_phone').value = button.dataset.phone ?? '';
    document.getElementById('interview_date').value = button.dataset.date ?? '';
    document.getElementById('interview_time').value = button.dataset.time ?? '';
    document.getElementById('interview_result').value = button.dataset.result ?? 'Chờ kết quả';
    document.getElementById('interview_notes').value = button.dataset.notes ?? '';
    document.getElementById('interviewSubmitBtn').textContent = 'Cập nhật lịch phỏng vấn';
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.addEventListener('DOMContentLoaded', () => {
    filterPeriods();

    const periodForm = document.getElementById('periodForm');
    if (periodForm) {
        setPeriodFormVisibility(!periodForm.classList.contains('hidden'));
    }

    const tabFromQuery = new URLSearchParams(window.location.search).get('tab');
    const tabFromStorage = localStorage.getItem(TAB_STORAGE_KEY);
    const initialTab = tabFromQuery || "{{ $activeTab }}" || tabFromStorage || 'jobs';
    activateTab(initialTab);
    localStorage.setItem(TAB_STORAGE_KEY, initialTab);

    const interviewEmailInput = document.getElementById('interview_candidate_email');
    if (interviewEmailInput) {
        interviewEmailInput.addEventListener('change', function () {
            const option = document.querySelector(`#candidateEmails option[value="${this.value}"]`);
            if (!option) {
                return;
            }
            document.getElementById('interview_candidate_name').value = option.dataset.name ?? '';
            document.getElementById('interview_candidate_phone').value = option.dataset.phone ?? '';
        });
    }

    const candidateSearchInput = document.getElementById('candidateSearchInput');
    if (candidateSearchInput) {
        candidateSearchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyCandidateFilters();
            }
        });
    }

    const interviewSearchInput = document.getElementById('interviewSearchInput');
    if (interviewSearchInput) {
        interviewSearchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyInterviewFilters();
            }
        });
    }

    resetCandidateForm();
});
</script>

@endsection
