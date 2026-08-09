<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CandidateStatusUpdatedNotification extends Notification
{
    public function __construct(
        protected string $status,
        protected ?string $position,
        protected string $source
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $positionText = $this->position ? ' cho vị trí "' . $this->position . '"' : '';

        return [
            'type' => 'candidate_status_updated',
            'status' => $this->status,
            'position' => $this->position,
            'source' => $this->source,
            'message' => 'Trạng thái hồ sơ' . $positionText . ' đã được cập nhật thành: ' . $this->status . '.',
        ];
    }
}
