<?php

namespace App\Notifications;

use App\Models\JobPosting;
use Illuminate\Notifications\Notification;

class JobPostingDeletedNotification extends Notification
{
    protected $jobPosting;

    public function __construct(JobPosting $jobPosting)
    {
        $this->jobPosting = $jobPosting;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'job_title' => $this->jobPosting->title,
            'job_id' => $this->jobPosting->job_id,
            'message' => 'Vị trí tuyển dụng "' . $this->jobPosting->title . '" đã bị hủy.',
            'type' => 'job_posting_deleted',
        ];
    }
}
