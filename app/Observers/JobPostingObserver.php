<?php

namespace App\Observers;

use App\Models\JobPosting;
use App\Models\Candidate;
use App\Notifications\JobPostingDeletedNotification;

class JobPostingObserver
{
    private const JOB_DELETED_NOTE_PREFIX = '[JOB_DELETED]';

    public function deleting(JobPosting $jobPosting): void
    {
        // Hỗ trợ cả dữ liệu cũ (job_id null nhưng position_applied trùng title)
        $candidates = Candidate::query()
            ->with('user')
            ->where(function ($query) use ($jobPosting) {
                $query->where('job_id', $jobPosting->job_id)
                    ->orWhere(function ($legacyQuery) use ($jobPosting) {
                        $legacyQuery->whereNull('job_id')
                            ->where('position_applied', $jobPosting->title);
                    });
            })
            ->get();

        if ($candidates->isEmpty()) {
            return;
        }

        // Gửi thông báo cho mỗi ứng viên
        foreach ($candidates as $candidate) {
            if ($candidate->user) {
                $candidate->user->notify(new JobPostingDeletedNotification($jobPosting));
            }
        }

        // Không xóa phỏng vấn/ứng viên nữa. Giữ dữ liệu lịch sử và gắn cờ ghi chú nếu cần.
        foreach ($candidates as $candidate) {
            $candidate->interviews()->each(function ($interview) use ($jobPosting) {
                $existingNotes = (string) ($interview->notes ?? '');
                if (str_contains($existingNotes, self::JOB_DELETED_NOTE_PREFIX)) {
                    return;
                }

                $deletedNote = self::JOB_DELETED_NOTE_PREFIX . ' Job "' . $jobPosting->title . '" đã bị xóa.';
                $interview->update([
                    'notes' => trim($existingNotes === '' ? $deletedNote : ($existingNotes . "\n" . $deletedNote)),
                ]);
            });
        }
    }
}
