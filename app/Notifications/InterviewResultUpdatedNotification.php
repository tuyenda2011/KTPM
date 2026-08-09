<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class InterviewResultUpdatedNotification extends Notification
{
    public function __construct(
        protected string $resultLabel,
        protected ?string $position,
        protected ?string $scheduledAt
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $positionText = $this->position ? ' cho vị trí "' . $this->position . '"' : '';
        $timeText = $this->scheduledAt ? ' (lịch: ' . $this->scheduledAt . ')' : '';

        return [
            'type' => 'interview_result_updated',
            'result' => $this->resultLabel,
            'position' => $this->position,
            'scheduled_at' => $this->scheduledAt,
            'message' => 'Kết quả phỏng vấn' . $positionText . ' đã được cập nhật: ' . $this->resultLabel . $timeText . '.',
        ];
    }
}
