<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Employee;
use App\Models\Department;
use App\Models\RecruitmentPeriod;
use App\Models\User;
use App\Notifications\CandidateStatusUpdatedNotification;
use App\Notifications\InterviewResultUpdatedNotification;
use App\Notifications\JobPostingDeletedNotification;
use App\Support\CandidateBackup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RecruitmentController extends Controller
{
    private const JOB_DELETED_NOTE_PREFIX = '[JOB_DELETED]';

    private function extractCvPathFromNotes(?string $notes): ?string
    {
        if (!is_string($notes) || trim($notes) === '') {
            return null;
        }

        if (preg_match('/(?:^|\r\n|\r|\n)CV:\s*([^\r\n]+)/u', $notes, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function hasIsDeletedColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('job_postings', 'is_deleted');
        }

        return $hasColumn;
    }

    private function hasRecruitmentPeriodColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('job_postings', 'recruitment_period_id');
        }

        return $hasColumn;
    }

    private function hasRecruitmentPeriodsTable(): bool
    {
        static $hasTable = null;

        if ($hasTable === null) {
            $hasTable = Schema::hasTable('recruitment_periods');
        }

        return $hasTable;
    }

    private function normalizeCandidateStatus(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $statusMap = [
            'Đậu' => 'Đã nhận việc',
            'Nhận việc' => 'Đã nhận việc',
            'Rớt' => 'Từ chối',
        ];

        return $statusMap[$status] ?? $status;
    }

    private function interviewResultLabel(string $result): string
    {
        $resultMap = [
            'pass' => 'Đã nhận việc',
            'fail' => 'Từ chối',
            'pending' => 'Chờ kết quả',
        ];

        return $resultMap[$result] ?? $result;
    }

    private function normalizeJobStatus(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $statusMap = [
            'Đang tuyển' => 'active',
            'Đã đóng' => 'closed',
            'Đã tuyển đủ' => 'filled',
        ];

        return $statusMap[$status] ?? $status;
    }

    private function jobStatusVariants(string $status): array
    {
        $statusVariants = [
            'active' => ['active', 'Đang tuyển'],
            'closed' => ['closed', 'Đã đóng'],
            'filled' => ['filled', 'Đã tuyển đủ'],
        ];

        return $statusVariants[$status] ?? [$status];
    }

    private function buildRecruitmentRedirectParams(Request $request, string $tab, ?string $periodId = null): array
    {
        $resolvedPeriodId = $periodId ?? $request->input('period_id') ?? $request->query('period_id');
        $params = ['tab' => $tab];

        if (!empty($resolvedPeriodId)) {
            $params['period_id'] = $resolvedPeriodId;
        }

        return $params;
    }

    private function isDepartmentManager($user): bool
    {
        if (!$user || $user->role !== 'staff') {
            return false;
        }

        return \App\Models\Department::where('manager_user_id', $user->user_id)->exists();
    }

    private function managedDepartmentId($user): ?string
    {
        if (!$user) {
            return null;
        }

        return \App\Models\Department::where('manager_user_id', $user->user_id)->value('department_id');
    }

    private function managedDepartmentName($user): ?string
    {
        $departmentId = $this->managedDepartmentId($user);

        return $departmentId
            ? Department::where('department_id', $departmentId)->value('name')
            : null;
    }

    private function canManageJob($user, $job): bool
    {
        if (!$user || !$job) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if (!$this->isDepartmentManager($user)) {
            return false;
        }

        $managedDepartmentName = $this->managedDepartmentName($user);

        if (!$managedDepartmentName) {
            return false;
        }

        if ($this->hasRecruitmentPeriodsTable() && $this->hasRecruitmentPeriodColumn()) {
            $job->loadMissing('recruitmentPeriod');
        }

        return trim((string) $job->department) === trim((string) $managedDepartmentName);
    }

    private function canManageCandidate($user, $candidate): bool
    {
        if (!$user || !$candidate) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if (!$this->isDepartmentManager($user)) {
            return false;
        }

        $managedDepartmentName = $this->managedDepartmentName($user);

        $candidate->loadMissing('job.recruitmentPeriod');

        if (!$managedDepartmentName || !$candidate->job) {
            return false;
        }

        if ($this->hasRecruitmentPeriodsTable() && $this->hasRecruitmentPeriodColumn()) {
            $candidate->job->loadMissing('recruitmentPeriod');
        }

        return trim((string) $candidate->job->department) === trim((string) $managedDepartmentName);
    }

    private function canManageInterview($user, $interview): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $this->canManageCandidate($user, $interview->candidate);
    }

    private function isCandidateInDeletedJob($candidate): bool
    {
        if (!$candidate) {
            return false;
        }

        $candidate->loadMissing('job');

        return $this->hasIsDeletedColumn()
            && $candidate->job
            && (bool) $candidate->job->is_deleted;
    }

    public function index(Request $request)
    {
        $supportsRecruitmentPeriods = $this->hasRecruitmentPeriodsTable() && $this->hasRecruitmentPeriodColumn();

        $currentUser = auth()->user();

        if (!$currentUser) {
            abort(403, 'Bạn không có quyền truy cập module tuyển dụng');
        }

        $isDepartmentManager = $this->isDepartmentManager($currentUser);
        $managedDepartmentName = $this->managedDepartmentName($currentUser);
        $periods = collect();
        if ($this->hasRecruitmentPeriodsTable()) {
            $periods = RecruitmentPeriod::query()
                ->whereIn('status', ['open', 'closed'])
                ->withCount([
                    'jobPostings as total_jobs_count' => function ($query) {
                        if ($this->hasIsDeletedColumn()) {
                            $query->where('is_deleted', false);
                        }
                    },
                    'jobPostings as active_jobs_count' => function ($query) {
                        $query->whereIn('status', $this->jobStatusVariants('active'));
                        if ($this->hasIsDeletedColumn()) {
                            $query->where('is_deleted', false);
                        }
                    },
                    'jobPostings as filled_jobs_count' => function ($query) {
                        $query->whereIn('status', $this->jobStatusVariants('filled'));
                        if ($this->hasIsDeletedColumn()) {
                            $query->where('is_deleted', false);
                        }
                    },
                    'jobPostings as closed_jobs_count' => function ($query) {
                        $query->whereIn('status', $this->jobStatusVariants('closed'));
                        if ($this->hasIsDeletedColumn()) {
                            $query->where('is_deleted', false);
                        }
                    },
                ])
                ->orderByDesc('start_date')
                ->orderByDesc('updated_at')
                ->get();
        }

        $selectedPeriodId = (string) $request->query('period_id', '');
        $selectedPeriod = $selectedPeriodId !== ''
            ? $periods->firstWhere('period_id', $selectedPeriodId)
            : null;

        $jobSearch = trim((string) $request->query('job_search', ''));
        $jobStatus = $this->normalizeJobStatus(trim((string) $request->query('job_status', ''))) ?? '';

        $jobPostingsQuery = JobPosting::query();

        if ($supportsRecruitmentPeriods) {
            $jobPostingsQuery->where(function ($query) {
                $query->whereNull('recruitment_period_id')
                    ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                        $periodQuery->whereIn('status', ['open', 'closed']);
                    });
            });
        }

        if ($supportsRecruitmentPeriods && $selectedPeriod) {
            $jobPostingsQuery->where('recruitment_period_id', $selectedPeriod->period_id);
        }

        if ($this->hasIsDeletedColumn()) {
            $jobPostingsQuery->orderBy('is_deleted');
        }

        if ($jobSearch !== '') {
            $jobPostingsQuery->where(function ($query) use ($jobSearch) {
                $query->where('title', 'like', '%' . $jobSearch . '%')
                    ->orWhere('job_id', 'like', '%' . $jobSearch . '%');
            });
        }

        if ($jobStatus !== '') {
            if ($jobStatus === 'deleted' && $this->hasIsDeletedColumn()) {
                $jobPostingsQuery->where('is_deleted', true);
            } else {
                $jobPostingsQuery->whereIn('status', $this->jobStatusVariants($jobStatus));
                if ($this->hasIsDeletedColumn()) {
                    $jobPostingsQuery->where('is_deleted', false);
                }
            }
        }

        $jobPostings = $jobPostingsQuery
            ->orderByDesc('updated_at')
            ->orderByDesc('job_id')
            ->paginate(10, ['*'], 'jobs_page')
            ->withQueryString();

        $allJobsCountQuery = JobPosting::query();
        if ($supportsRecruitmentPeriods) {
            $allJobsCountQuery->where(function ($query) {
                $query->whereNull('recruitment_period_id')
                    ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                        $periodQuery->whereIn('status', ['open', 'closed']);
                    });
            });
        }

        if ($this->hasIsDeletedColumn()) {
            $allJobsCountQuery->where('is_deleted', false);
        }

        $allJobsCount = (clone $allJobsCountQuery)->count();
        $allOpenJobsCount = (clone $allJobsCountQuery)
            ->whereIn('status', $this->jobStatusVariants('active'))
            ->count();

        $closedAndFilledVariants = array_values(array_unique(array_merge(
            $this->jobStatusVariants('closed'),
            $this->jobStatusVariants('filled')
        )));

        $allClosedJobsCount = (clone $allJobsCountQuery)
            ->whereIn('status', $closedAndFilledVariants)
            ->count();

        $activeJobPostingsQuery = JobPosting::whereIn('status', $this->jobStatusVariants('active'));
        if ($supportsRecruitmentPeriods) {
            $activeJobPostingsQuery->where(function ($query) {
                $query->whereNull('recruitment_period_id')
                    ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                        $periodQuery->whereIn('status', ['open', 'closed']);
                    });
            });
        }

        if ($this->hasIsDeletedColumn()) {
            $activeJobPostingsQuery->where('is_deleted', false);
        }
        if ($supportsRecruitmentPeriods && $selectedPeriod) {
            $activeJobPostingsQuery->where('recruitment_period_id', $selectedPeriod->period_id);
        }

        $activeJobPostings = $activeJobPostingsQuery
            ->orderBy('title')
            ->get();

        $candidatesQuery = Candidate::with(['user', 'job']);
        if ($supportsRecruitmentPeriods) {
            $candidatesQuery->where(function ($query) {
                $query->whereNull('job_id')
                    ->orWhereHas('job', function ($jobQuery) {
                        $jobQuery->whereNull('recruitment_period_id')
                            ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                                $periodQuery->whereIn('status', ['open', 'closed']);
                            });
                    });
            });
        }

        if ($this->hasIsDeletedColumn()) {
            $candidatesQuery->orderByRaw("CASE WHEN EXISTS (SELECT 1 FROM job_postings jp WHERE jp.job_id = candidates.job_id AND jp.is_deleted = 1) THEN 1 ELSE 0 END");
        }
        if ($supportsRecruitmentPeriods && $selectedPeriod) {
            $candidatesQuery->whereHas('job', function ($query) use ($selectedPeriod) {
                $query->where('recruitment_period_id', $selectedPeriod->period_id);
            });
        }
        $candidates = $candidatesQuery
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'candidates_page')
            ->withQueryString();

        $interviewsQuery = Interview::with(['candidate', 'job', 'interviewer']);
        if ($supportsRecruitmentPeriods) {
            $interviewsQuery->where(function ($query) {
                $query->whereDoesntHave('job')
                    ->orWhereHas('job', function ($jobQuery) {
                        $jobQuery->whereNull('recruitment_period_id')
                            ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                                $periodQuery->whereIn('status', ['open', 'closed']);
                            });
                    });
            });
        }

        if ($this->hasIsDeletedColumn()) {
            $interviewsQuery->orderByRaw("CASE WHEN EXISTS (SELECT 1 FROM candidates c JOIN job_postings jp ON jp.job_id = c.job_id WHERE c.user_id = interviews.user_id AND jp.is_deleted = 1) OR notes LIKE '%[JOB_DELETED]%' THEN 1 ELSE 0 END");
        }
        if ($supportsRecruitmentPeriods && $selectedPeriod) {
            $interviewsQuery->whereHas('job', function ($query) use ($selectedPeriod) {
                $query->where('recruitment_period_id', $selectedPeriod->period_id);
            });
        }
        $interviews = $interviewsQuery
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'interviews_page')
            ->withQueryString();

        $interviewCandidatesQuery = Candidate::with('user')->where('status', 'Phỏng vấn');
        if ($supportsRecruitmentPeriods) {
            $interviewCandidatesQuery->where(function ($query) {
                $query->whereNull('job_id')
                    ->orWhereHas('job', function ($jobQuery) {
                        $jobQuery->whereNull('recruitment_period_id')
                            ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                                $periodQuery->whereIn('status', ['open', 'closed']);
                            });
                    });
            });
        }

        if ($supportsRecruitmentPeriods && $selectedPeriod) {
            $interviewCandidatesQuery->whereHas('job', function ($query) use ($selectedPeriod) {
                $query->where('recruitment_period_id', $selectedPeriod->period_id);
            });
        }
        $interviewCandidates = $interviewCandidatesQuery->get();

        $candidatePositions = Candidate::query()
            ->whereNotNull('position_applied')
            ->where('position_applied', '!=', '')
            ->when($supportsRecruitmentPeriods, function ($query) {
                $query->where(function ($scopedQuery) {
                    $scopedQuery->whereNull('job_id')
                        ->orWhereHas('job', function ($jobQuery) {
                            $jobQuery->whereNull('recruitment_period_id')
                                ->orWhereHas('recruitmentPeriod', function ($periodQuery) {
                                    $periodQuery->whereIn('status', ['open', 'closed']);
                                });
                        });
                });
            })
            ->distinct()
            ->orderBy('position_applied')
            ->pluck('position_applied');

        $employees = Employee::where('status', 'Đang làm')->get();

        $hiringCandidatesQuery = Candidate::with([
            'user',
            'job',
            'interviews' => fn ($query) => $query->orderByDesc('scheduled_at'),
        ])->whereHas('interviews', fn ($query) => $query->where('result', 'pass'));

        if ($currentUser->role === 'staff') {
            $hiringCandidates = collect();
        } else {
            if ($supportsRecruitmentPeriods && $selectedPeriod) {
                $hiringCandidatesQuery->whereHas('job', function ($query) use ($selectedPeriod) {
                    $query->where('recruitment_period_id', $selectedPeriod->period_id);
                });
            }

            $hiringCandidates = $hiringCandidatesQuery
                ->orderByDesc('updated_at')
                ->paginate(10, ['*'], 'hiring_page')
                ->withQueryString();
        }

        $departments = Department::orderBy('name')->get();

        return view('recruitment', [
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'jobPostings' => $jobPostings,
            'jobSearch' => $jobSearch,
            'jobStatus' => $jobStatus,
            'supportsRecruitmentPeriods' => $supportsRecruitmentPeriods,
            'allJobsCount' => $allJobsCount,
            'allOpenJobsCount' => $allOpenJobsCount,
            'allClosedJobsCount' => $allClosedJobsCount,
            'activeJobPostings' => $activeJobPostings,
            'candidates' => $candidates,
            'candidatePositions' => $candidatePositions,
            'interviews' => $interviews,
            'interviewCandidates' => $interviewCandidates,
            'employees' => $employees,
            'hiringCandidates' => $hiringCandidates,
            'departments' => $departments,
            'isDepartmentManager' => $isDepartmentManager,
            'managedDepartmentName' => $managedDepartmentName,
        ]);
    }

    // ===== RECRUITMENT PERIODS =====
    public function storePeriod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:open,closed',
            'notes' => 'nullable|string',
        ]);

        $period = RecruitmentPeriod::create($validated);

        return redirect()->route('recruitment.index', ['period_id' => $period->period_id, 'tab' => 'jobs'])
            ->with('success', 'Tạo kỳ tuyển dụng thành công');
    }

    public function updatePeriod(Request $request, string $periodId)
    {
        $period = RecruitmentPeriod::where('period_id', $periodId)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:open,closed',
            'notes' => 'nullable|string',
        ]);

        $period->update($validated);

        return redirect()->route('recruitment.index', ['period_id' => $period->period_id, 'tab' => 'jobs'])
            ->with('success', 'Cập nhật kỳ tuyển dụng thành công');
    }

    public function destroyPeriod(string $periodId)
    {
        $period = RecruitmentPeriod::where('period_id', $periodId)->firstOrFail();

        if (JobPosting::where('recruitment_period_id', $period->period_id)->exists()) {
            return redirect()->route('recruitment.index', ['period_id' => $period->period_id, 'tab' => 'jobs'])
                ->withErrors(['period' => 'Kỳ tuyển dụng vẫn còn tin tuyển dụng, không thể xóa.']);
        }

        $period->delete();

        $nextPeriodId = RecruitmentPeriod::query()
            ->orderByDesc('start_date')
            ->value('period_id');

        $params = $nextPeriodId ? ['period_id' => $nextPeriodId, 'tab' => 'jobs'] : ['tab' => 'jobs'];

        return redirect()->route('recruitment.index', $params)
            ->with('success', 'Đã xóa kỳ tuyển dụng thành công');
    }

    // ===== JOB POSTINGS =====
    public function storeJob(Request $request)
    {
        $periodValidationRules = $this->hasRecruitmentPeriodColumn()
            ? ['required', Rule::exists('recruitment_periods', 'period_id')]
            : ['nullable'];

        $validated = $request->validate([
            'recruitment_period_id' => $periodValidationRules,
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary_min' => 'required|integer|min:0',
            'salary_max' => 'required|integer|gte:salary_min|min:0',
            'quantity' => 'required|integer',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'deadline' => 'required|date',
            'status' => 'required|in:active,closed,filled,Đang tuyển,Đã đóng,Đã tuyển đủ',
        ]);

        $currentUser = auth()->user();

        if ($currentUser->role !== 'admin') {
            if (!$this->isDepartmentManager($currentUser)) {
                abort(403, 'Bạn không có quyền tạo tin tuyển dụng');
            }

            $managedDepartmentName = $this->managedDepartmentName($currentUser);

            if (!$managedDepartmentName) {
                abort(403, 'Không xác định được phòng ban quản lý');
            }

            $validated['department'] = $managedDepartmentName;

        }

        $statusMap = [
            'Đang tuyển' => 'active',
            'Đã đóng' => 'closed',
            'Đã tuyển đủ' => 'filled',
        ];

        $validated['status'] = $statusMap[$validated['status']] ?? $validated['status'];

        if ($this->hasIsDeletedColumn()) {
            $validated['is_deleted'] = false;
        }

        JobPosting::create($validated);

        $redirectPeriodId = $validated['recruitment_period_id'] ?? null;

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'jobs', $redirectPeriodId))
            ->with('success', 'Thêm tin tuyển dụng thành công');
    }

    public function updateJob(Request $request, $jobId)
    {
        $job = JobPosting::where('job_id', $jobId)->firstOrFail();

        $redirectPeriodId = (string) ($request->input('recruitment_period_id') ?: $job->recruitment_period_id);

        $currentUser = auth()->user();

        if (!$this->canManageJob($currentUser, $job)) {
            abort(403, 'Bạn không có quyền sửa tin tuyển dụng này');
        }

        if ($this->hasIsDeletedColumn() && $job->isDeleted()) {
            return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'jobs', $redirectPeriodId))->withErrors([
                'job' => 'Tin tuyển dụng đã xóa chỉ có thể xem, không thể chỉnh sửa.',
            ]);
        }

        $periodValidationRules = $this->hasRecruitmentPeriodColumn()
            ? ['required', Rule::exists('recruitment_periods', 'period_id')]
            : ['nullable'];

        $validated = $request->validate([
            'recruitment_period_id' => $periodValidationRules,
            'title' => 'sometimes|required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'salary_min' => 'sometimes|required|integer|min:0',
            'salary_max' => 'sometimes|required|integer|min:0',
            'quantity' => 'sometimes|required|integer',
            'description' => 'sometimes|required|string',
            'requirements' => 'sometimes|required|string',
            'deadline' => 'sometimes|required|date',
            'status' => 'required|in:active,closed,filled,Đang tuyển,Đã đóng,Đã tuyển đủ',
        ]);

        if ($currentUser->role !== 'admin') {
            $managedDepartmentName = $this->managedDepartmentName($currentUser);

            if (!$managedDepartmentName) {
                abort(403, 'Không xác định được phòng ban quản lý');
            }

            $validated['department'] = $managedDepartmentName;

        }


        $statusMap = [
            'Đang tuyển' => 'active',
            'Đã đóng' => 'closed',
            'Đã tuyển đủ' => 'filled',
        ];
        $salaryMin = $validated['salary_min'] ?? $job->salary_min;
        $salaryMax = $validated['salary_max'] ?? $job->salary_max;

        if ($salaryMax < $salaryMin) {
            return back()->withErrors([
                'salary_max' => 'Mức lương đến phải lớn hơn hoặc bằng mức lương từ.',
            ])->withInput();
        }

        $job->update([
            'recruitment_period_id' => $validated['recruitment_period_id'] ?? $job->recruitment_period_id,
            'title' => $validated['title'] ?? $job->title,
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'quantity' => $validated['quantity'] ?? $job->quantity,
            'description' => $validated['description'] ?? $job->description,
            'requirements' => $validated['requirements'] ?? $job->requirements,
            'deadline' => $validated['deadline'] ?? $job->deadline,
            'status' => $statusMap[$validated['status']] ?? $validated['status'],
            'department' => $validated['department'] ?? $job->department,
            'location' => $validated['location'] ?? $job->location,
        ]);

        return redirect()->route(
            'recruitment.index',
            $this->buildRecruitmentRedirectParams($request, 'jobs', $validated['recruitment_period_id'] ?? $job->recruitment_period_id)
        )
            ->with('success', 'Cập nhật tin tuyển dụng thành công');
    }

    public function destroyJob(Request $request, $jobId)
    {
        $job = JobPosting::where('job_id', $jobId)->firstOrFail();
        
        $currentUser = auth()->user();

        if (!$this->canManageJob($currentUser, $job)) {
            abort(403, 'Bạn không có quyền xóa tin tuyển dụng này');
        }
        $redirectPeriodId = (string) ($request->input('period_id') ?: $job->recruitment_period_id);

        if ($this->hasIsDeletedColumn() && $job->isDeleted()) {
            return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'jobs', $redirectPeriodId))->with(
                'success',
                'Tin tuyển dụng này đã ở trạng thái đã xóa.'
            );
        }

        $candidates = Candidate::query()
            ->with('user')
            ->where(function ($query) use ($job) {
                $query->where('job_id', $job->job_id)
                    ->orWhere(function ($legacyQuery) use ($job) {
                        $legacyQuery->whereNull('job_id')
                            ->where('position_applied', $job->title);
                    });
            })
            ->get();

        $candidateUserIds = $candidates->pluck('user_id');

        foreach ($candidates as $candidate) {
            $candidateUpdatePayload = ['status' => 'Đã xóa'];

            if (empty($candidate->job_id)) {
                $candidateUpdatePayload['job_id'] = $job->job_id;
            }

            $candidate->update($candidateUpdatePayload);

            if ($candidate->user) {
                $candidate->user->notify(new JobPostingDeletedNotification($job));
            }
        }

        $relatedCandidates = $candidateUserIds->count();
        $relatedInterviews = $relatedCandidates > 0
            ? Interview::whereIn('user_id', $candidateUserIds)->count()
            : 0;

        if ($relatedCandidates > 0) {
            $interviews = Interview::whereIn('user_id', $candidateUserIds)->get();
            foreach ($interviews as $interview) {
                $existingNotes = (string) ($interview->notes ?? '');
                if (str_contains($existingNotes, self::JOB_DELETED_NOTE_PREFIX)) {
                    continue;
                }

                $deletedNote = self::JOB_DELETED_NOTE_PREFIX . ' Job "' . $job->title . '" đã bị xóa.';
                $interview->update([
                    'notes' => trim($existingNotes === '' ? $deletedNote : ($existingNotes . "\n" . $deletedNote)),
                ]);
            }
        }

        $jobUpdatePayload = ['status' => 'closed'];
        if ($this->hasIsDeletedColumn()) {
            $jobUpdatePayload['is_deleted'] = true;
        }

        $job->update($jobUpdatePayload);

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'jobs', $redirectPeriodId))->with(
            'success',
            "Đã chuyển tin tuyển dụng sang trạng thái Đã xóa. Giữ lại {$relatedCandidates} ứng viên và {$relatedInterviews} lịch phỏng vấn liên quan."
        );
    }

    // ===== CANDIDATES =====
    public function storeCandidate(Request $request)
    {
        $currentUser = auth()->user();

        if (!$currentUser || $currentUser->role !== 'admin') {
            abort(403, 'Bạn không có quyền thêm ứng viên');
        }
    
        $validated = $request->validate([
            'job_id' => [
                'nullable',
                Rule::exists('job_postings', 'job_id')->where(function ($query) {
                    $query->where('status', 'active');
                    if ($this->hasIsDeletedColumn()) {
                        $query->where('is_deleted', false);
                    }
                }),
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:100',
            'status' => 'required|in:Đang chờ,Đã duyệt CV,Phỏng vấn,Đã nhận việc,Nhận việc,Từ chối',
            'cv_path' => 'nullable|file|max:5120',
            'applied_date' => 'nullable|date',
        ]);

        $statusMap = [
            'Đậu' => 'Đã nhận việc',
            'Rớt' => 'Từ chối',
            'Nhận việc' => 'Đã nhận việc',
        ];
        $candidateStatus = $statusMap[$validated['status']] ?? $validated['status'];

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            $user = User::create([
                'user_id' => $this->generateNextUserId('US'),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(12)),
                'role' => 'user',
                'phone' => $validated['phone'],
            ]);
        } else {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
            ]);
        }

        // Xử lý upload CV theo thư mục riêng của ứng viên
        $cvPath = null;
        if ($request->hasFile('cv_path')) {
            $cvPath = $this->storeCandidateCv($request->file('cv_path'), $validated['name'], $user->user_id);
        }

        $noteText = $cvPath ? 'CV: ' . $cvPath : null;

        $candidateRecord = Candidate::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'job_id' => $validated['job_id'] ?? null,
                'position_applied' => $validated['position'],
                'status' => $candidateStatus,
                'applied_date' => $validated['applied_date'] ?? now()->toDateString(),
                'notes' => $noteText,
            ]
        );

        CandidateBackup::upsert($candidateRecord->user_id, [
            'user' => [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birth_date' => $user->birth_date,
                'address' => $user->address,
            ],
            'candidate' => [
                'job_id' => $candidateRecord->job_id,
                'position_applied' => $candidateRecord->position_applied,
                'status' => $candidateRecord->status,
                'experience' => $candidateRecord->experience,
                'education' => $candidateRecord->education,
                'notes' => $candidateRecord->notes,
                'applied_date' => $candidateRecord->applied_date,
            ],
            'updated_at' => now()->toDateTimeString(),
        ]);

        $this->ensureInterviewRecordForCandidate($user->user_id, $candidateStatus);

        $targetTab = $candidateStatus === 'Phỏng vấn' ? 'interviews' : 'candidates';
        $redirectPeriodId = null;
        if ($targetTab === 'interviews' && $this->hasRecruitmentPeriodColumn()) {
            $redirectPeriodId = JobPosting::where('job_id', $candidateRecord->job_id)->value('recruitment_period_id');
        }

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, $targetTab, $redirectPeriodId))
            ->with('success', 'Thêm ứng viên thành công');
    }

    public function updateCandidate(Request $request, $candidateId)
    {
        $candidate = Candidate::where('user_id', $candidateId)->firstOrFail();
        
        $currentUser = auth()->user();

        if (!$this->canManageCandidate($currentUser, $candidate)) {
            abort(403, 'Bạn không có quyền cập nhật ứng viên này');
        }

        if ($this->isCandidateInDeletedJob($candidate)) {
            return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'candidates'))
                ->with('error', 'Ứng viên thuộc tin tuyển dụng đã xóa. Trạng thái hồ sơ được khóa ở mức Đã xóa.');
        }

        $oldStatus = $this->normalizeCandidateStatus($candidate->status);

        $validated = $request->validate([
            'job_id' => [
                'nullable',
                Rule::exists('job_postings', 'job_id')->where(function ($query) {
                    $query->where('status', 'active');
                    if ($this->hasIsDeletedColumn()) {
                        $query->where('is_deleted', false);
                    }
                }),
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:100',
            'status' => 'required|in:Đang chờ,Đã duyệt CV,Phỏng vấn,Đã nhận việc,Nhận việc,Từ chối',
            'cv_path' => 'nullable|file|max:5120',
            'applied_date' => 'nullable|date',
        ]);

        if ($currentUser->role !== 'admin' && !empty($validated['job_id'])) {
            $targetJob = JobPosting::where('job_id', $validated['job_id'])->first();

            if (!$targetJob || !$this->canManageJob($currentUser, $targetJob)) {
                abort(403, 'Bạn không có quyền gán ứng viên sang tin tuyển dụng của phòng khác');
            }
        }

        $statusMap = [
            'Đậu' => 'Đã nhận việc',
            'Rớt' => 'Từ chối',
            'Nhận việc' => 'Đã nhận việc',
        ];
        $candidateStatus = $statusMap[$validated['status']] ?? $validated['status'];

        // Xử lý upload CV theo thư mục riêng của ứng viên
        $cvPath = null;
        if ($request->hasFile('cv_path')) {
            $cvPath = $this->storeCandidateCv($request->file('cv_path'), $validated['name'], $candidate->user_id);
        }

        $candidate->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $candidate->update([
            'job_id' => $validated['job_id'] ?? null,
            'position_applied' => $validated['position'],
            'status' => $candidateStatus,
            'applied_date' => $validated['applied_date'] ?? $candidate->applied_date,
            'notes' => $cvPath ? 'CV: ' . $cvPath : $candidate->notes,
        ]);

        CandidateBackup::upsert($candidate->user_id, [
            'user' => [
                'user_id' => $candidate->user?->user_id,
                'name' => $candidate->user?->name,
                'email' => $candidate->user?->email,
                'phone' => $candidate->user?->phone,
                'gender' => $candidate->user?->gender,
                'birth_date' => $candidate->user?->birth_date,
                'address' => $candidate->user?->address,
            ],
            'candidate' => [
                'job_id' => $candidate->job_id,
                'position_applied' => $candidate->position_applied,
                'status' => $candidate->status,
                'experience' => $candidate->experience,
                'education' => $candidate->education,
                'notes' => $candidate->notes,
                'applied_date' => $candidate->applied_date,
            ],
            'updated_at' => now()->toDateTimeString(),
        ]);

        $this->ensureInterviewRecordForCandidate($candidate->user_id, $candidateStatus);

        $targetTab = $candidateStatus === 'Phỏng vấn' ? 'interviews' : 'candidates';
        $redirectPeriodId = null;
        if ($targetTab === 'interviews' && $this->hasRecruitmentPeriodColumn()) {
            $redirectPeriodId = JobPosting::where('job_id', $candidate->job_id)->value('recruitment_period_id');
        }

        if ($candidate->user && $oldStatus !== $candidateStatus) {
            $candidate->user->notify(new CandidateStatusUpdatedNotification(
                $candidateStatus,
                $candidate->position_applied ?? null,
                'quản lý ứng viên'
            ));
        }

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, $targetTab, $redirectPeriodId))
            ->with('success', 'Cập nhật ứng viên thành công');
    }

    public function destroyCandidate(Request $request, $candidateId)
    {
        $candidate = Candidate::where('user_id', $candidateId)->firstOrFail();
        $currentUser = auth()->user();

        if (!$currentUser || $currentUser->role !== 'admin') {
            abort(403, 'Bạn không có quyền xóa ứng viên này');
        }

        CandidateBackup::remove($candidate->user_id);
        $candidate->delete();

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'candidates'))
            ->with('success', 'Xóa ứng viên thành công');
    }

    public function serveCandidateCv(string $candidateId)
    {
        $candidate = Candidate::where('user_id', $candidateId)->firstOrFail();
        $currentUser = auth()->user();

        if (!$currentUser) {
            abort(403, 'Bạn không có quyền xem CV của ứng viên này');
        }

        $cvPath = $this->extractCvPathFromNotes($candidate->notes);

        if (!$cvPath || !Storage::disk('public')->exists($cvPath)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($cvPath));
    }

    // ===== INTERVIEWS =====
    public function storeInterview(Request $request)
    {
        $validated = $request->validate([
            'candidate_email' => 'required|email|exists:users,email',
            'interview_date' => 'required|date',
            'interview_time' => 'required|date_format:H:i',
            'result' => 'required|in:Đã nhận việc,Nhận việc,Từ chối,Đậu,Rớt,Chờ kết quả,pass,fail,pending',
            'notes' => 'nullable|string',
        ]);

        $user = User::where('email', $validated['candidate_email'])->firstOrFail();
        $candidate = Candidate::where('user_id', $user->user_id)->firstOrFail();
        

        $currentUser = auth()->user();

        if (!$this->canManageCandidate($currentUser, $candidate)) {
            abort(403, 'Bạn không có quyền tạo lịch phỏng vấn cho ứng viên này');
        }

        if ($this->isCandidateInDeletedJob($candidate)) {
            return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'interviews'))
                ->with('error', 'Không thể tạo lịch phỏng vấn cho ứng viên thuộc tin tuyển dụng đã xóa.');
        }

        $oldCandidateStatus = $this->normalizeCandidateStatus($candidate->status);

        $resultMap = [
            'Đã nhận việc' => 'pass',
            'Nhận việc' => 'pass',
            'Từ chối' => 'fail',
            'Đậu' => 'pass',
            'Rớt' => 'fail',
            'Chờ kết quả' => 'pending',
        ];

        $result = $resultMap[$validated['result']] ?? $validated['result'];

        $scheduledAt = $validated['interview_date'] . ' ' . $validated['interview_time'] . ':00';

        Interview::create([
            'user_id' => $user->user_id,
            'scheduled_at' => $scheduledAt,
            'result' => $result,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update candidate status based on interview result
        $newCandidateStatus = $oldCandidateStatus;
        if ($result === 'pass') {
            $newCandidateStatus = 'Đã nhận việc';
            $candidate->update(['status' => $newCandidateStatus]);
        } elseif ($result === 'fail') {
            $newCandidateStatus = 'Từ chối';
            $candidate->update(['status' => $newCandidateStatus]);
        }

        if ($candidate->user) {
            $candidate->user->notify(new InterviewResultUpdatedNotification(
                $this->interviewResultLabel($result),
                $candidate->position_applied ?? null,
                $scheduledAt
            ));
        }

        if ($candidate->user && $oldCandidateStatus !== $newCandidateStatus) {
            $candidate->user->notify(new CandidateStatusUpdatedNotification(
                $newCandidateStatus,
                $candidate->position_applied ?? null,
                'quản lý phỏng vấn'
            ));
        }

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'interviews'))
            ->with('success', 'Thêm lịch phỏng vấn thành công');
    }

    public function updateInterview(Request $request, $interviewId)
    {
        $interview = Interview::where('interview_id', $interviewId)->firstOrFail();
        
        $currentUser = auth()->user();

        if (!$this->canManageInterview($currentUser, $interview)) {
            abort(403, 'Bạn không có quyền cập nhật lịch phỏng vấn này');
        }
        $oldResult = $interview->result;
        $oldScheduledAt = $interview->scheduled_at?->format('Y-m-d H:i:s');

        $validated = $request->validate([
            'candidate_email' => 'required|email|exists:users,email',
            'interview_date' => 'required|date',
            'interview_time' => 'required|date_format:H:i',
            'result' => 'required|in:Đã nhận việc,Nhận việc,Từ chối,Đậu,Rớt,Chờ kết quả,pass,fail,pending',
            'notes' => 'nullable|string',
        ]);

        $user = User::where('email', $validated['candidate_email'])->firstOrFail();
        $candidate = Candidate::where('user_id', $user->user_id)->firstOrFail();

        if ($this->isCandidateInDeletedJob($candidate)) {
            return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'interviews'))
                ->with('error', 'Không thể cập nhật lịch phỏng vấn cho ứng viên thuộc tin tuyển dụng đã xóa.');
        }

        if (!$this->canManageCandidate($currentUser, $candidate)) {
            abort(403, 'Bạn không có quyền gán lịch phỏng vấn cho ứng viên này');
        }
        $oldCandidateStatus = $this->normalizeCandidateStatus($candidate->status);

        $resultMap = [
            'Đã nhận việc' => 'pass',
            'Nhận việc' => 'pass',
            'Từ chối' => 'fail',
            'Đậu' => 'pass',
            'Rớt' => 'fail',
            'Chờ kết quả' => 'pending',
        ];

        $result = $resultMap[$validated['result']] ?? $validated['result'];

        $newScheduledAt = $validated['interview_date'] . ' ' . $validated['interview_time'] . ':00';

        $interview->update([
            'user_id' => $user->user_id,
            'scheduled_at' => $newScheduledAt,
            'result' => $result,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update candidate status based on interview result
        $newCandidateStatus = $oldCandidateStatus;
        if ($result === 'pass') {
            $newCandidateStatus = 'Đã nhận việc';
            $candidate->update(['status' => $newCandidateStatus]);
        } elseif ($result === 'fail') {
            $newCandidateStatus = 'Từ chối';
            $candidate->update(['status' => $newCandidateStatus]);
        } else {
            // Keep status as is for pending result
            $newCandidateStatus = 'Phỏng vấn';
            $candidate->update(['status' => $newCandidateStatus]);
        }

        if ($candidate->user && ($oldResult !== $result || $oldScheduledAt !== $newScheduledAt)) {
            $candidate->user->notify(new InterviewResultUpdatedNotification(
                $this->interviewResultLabel($result),
                $candidate->position_applied ?? null,
                $newScheduledAt
            ));
        }

        if ($candidate->user && $oldCandidateStatus !== $newCandidateStatus) {
            $candidate->user->notify(new CandidateStatusUpdatedNotification(
                $newCandidateStatus,
                $candidate->position_applied ?? null,
                'quản lý phỏng vấn'
            ));
        }

        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'interviews'))
            ->with('success', 'Cập nhật lịch phỏng vấn thành công');
    }

    public function destroyInterview(Request $request, $interviewId)
    {
        $interview = Interview::where('interview_id', $interviewId)->firstOrFail();
        $currentUser = auth()->user();

        if (!$this->canManageInterview($currentUser, $interview)) {
            abort(403, 'Bạn không có quyền xóa lịch phỏng vấn này');
        }
        $interview->delete();


        return redirect()->route('recruitment.index', $this->buildRecruitmentRedirectParams($request, 'interviews'))
            ->with('success', 'Xóa lịch phỏng vấn thành công');
    }

    # public function submitApplication(Request $request)
    # {
    #     $validated = $request->validate([
    #         'job_id' => 'required|exists:job_postings,job_id',
    #         'name' => 'required|string|max:255',
    #         'email' => 'required|email',
    #         'phone' => 'required|string|max:20',
    #         'position' => 'required|string|max:100',
    #         'cv_path' => 'required|file|max:5120',
    #      ]);

        // Xử lý upload CV
    #     if ($request->hasFile('cv_path')) {
    #         $validated['cv_path'] = $this->storeCandidateCv($request->file('cv_path'), $validated['name']);
    #     }
    #     $validated['status'] = 'Đang chờ';
    #     $validated['applied_date'] = now();

    #     Candidate::create($validated);

    #     return redirect()->back()->with('success', 'Nộp hồ sơ thành công');
    # }

    private function generateNextUserId(string $prefix): string
    {
        do {
            $candidateId = $prefix . '_' . (string) Str::ulid();
        } while (User::where('user_id', $candidateId)->exists());

        return $candidateId;
    }

    private function ensureInterviewRecordForCandidate(string $userId, string $status): void
    {
        if ($status !== 'Phỏng vấn') {
            return;
        }

        // When candidate is moved back to interview stage, ensure there is a pending interview row.
        // Do not rely on generic existence because user may only have historical pass/fail interviews.
        $hasPendingInterview = Interview::where('user_id', $userId)
            ->where('result', 'pending')
            ->exists();

        if (!$hasPendingInterview) {
            Interview::create([
                'user_id' => $userId,
                'scheduled_at' => null,
                'result' => 'pending',
                'notes' => 'Tạo tự động khi ứng viên chuyển sang trạng thái Phỏng vấn',
            ]);
        }
    }

    private function storeCandidateCv($uploadedFile, string $candidateName, string $candidateId): string
    {
        $safeCandidateId = preg_replace('/[^A-Za-z0-9_-]/', '_', $candidateId);
        $directory = 'candidates/' . ($safeCandidateId ?: 'unknown');
        $nameSlug = Str::slug($candidateName, '_');
        $nameSlug = $nameSlug !== '' ? $nameSlug : 'ung_vien';
        $baseName = 'cv_uv_' . $nameSlug;
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: 'pdf');

        $fileName = $baseName . '.' . $extension;
        $counter = 1;

        while (Storage::disk('public')->exists($directory . '/' . $fileName)) {
            $counter++;
            $fileName = $baseName . '_' . $counter . '.' . $extension;
        }

        return $uploadedFile->storeAs($directory, $fileName, 'public');
    }
}
