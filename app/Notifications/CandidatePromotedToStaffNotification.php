<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CandidatePromotedToStaffNotification extends Notification
{
    public function __construct(
        protected ?string $position,
        protected ?string $departmentName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $positionText = $this->position ? ' với vị trí ' . $this->position : '';
        $departmentText = $this->departmentName ? ' tại ' . $this->departmentName : '';

        return [
            'type' => 'candidate_promoted_to_staff',
            'position' => $this->position,
            'department' => $this->departmentName,
            'message' => 'Chúc mừng! Hồ sơ của bạn đã được tiếp nhận thành nhân viên' . $positionText . $departmentText . '.',
        ];
    }
}
