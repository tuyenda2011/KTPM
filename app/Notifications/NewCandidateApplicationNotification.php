<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class NewCandidateApplicationNotification extends Notification
{
    public function __construct(
        protected string $candidateName,
        protected string $candidateEmail,
        protected string $jobTitle,
        protected string $appliedAt
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'applied_at' => $this->appliedAt,
            'message' => 'Ứng viên ' . $this->candidateName . ' (' . $this->candidateEmail . ') vừa nộp hồ sơ cho vị trí "' . $this->jobTitle . '".',
        ];
    }
}