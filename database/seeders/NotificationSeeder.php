<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifiableType = User::class;
        $now = now();

        $samples = [
            [
                'id' => '11111111-1111-1111-1111-111111111001',
                'notifiable_type' => $notifiableType,
                'notifiable_id' => 'US_001',
                'type' => 'App\\Notifications\\CandidateStatusUpdatedNotification',
                'data' => json_encode([
                    'status' => 'Phỏng vấn',
                    'position' => 'Frontend Developer React',
                    'source' => 'quản lý ứng viên',
                    'message' => 'Trạng thái hồ sơ cho vị trí "Frontend Developer React" đã được cập nhật thành: Phỏng vấn.',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now->copy()->subHours(6),
            ],
            [
                'id' => '11111111-1111-1111-1111-111111111002',
                'notifiable_type' => $notifiableType,
                'notifiable_id' => 'US_001',
                'type' => 'App\\Notifications\\InterviewResultUpdatedNotification',
                'data' => json_encode([
                    'result' => 'Chờ kết quả',
                    'position' => 'Frontend Developer React',
                    'scheduled_at' => '2026-04-05 09:30:00',
                    'message' => 'Kết quả phỏng vấn cho vị trí "Frontend Developer React" đã được cập nhật: Chờ kết quả (lịch: 2026-04-05 09:30:00).',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now->copy()->subHours(4),
            ],
            [
                'id' => '11111111-1111-1111-1111-111111111003',
                'notifiable_type' => $notifiableType,
                'notifiable_id' => 'US_002',
                'type' => 'App\\Notifications\\JobPostingDeletedNotification',
                'data' => json_encode([
                    'job_title' => 'Content Creator',
                    'job_id' => 'JOB_006',
                    'message' => 'Vị trí tuyển dụng "Content Creator" đã bị hủy.',
                    'type' => 'job_posting_deleted',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => $now->copy()->subHours(2),
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subHours(2),
            ],
            [
                'id' => '11111111-1111-1111-1111-111111111004',
                'notifiable_type' => $notifiableType,
                'notifiable_id' => 'US_003',
                'type' => 'App\\Notifications\\CandidatePromotedToStaffNotification',
                'data' => json_encode([
                    'position' => 'Nhân viên Nhân sự',
                    'department' => 'Phòng Nhân Sự',
                    'message' => 'Chúc mừng! Hồ sơ của bạn đã được tiếp nhận thành nhân viên với vị trí Nhân viên Nhân sự tại Phòng Nhân Sự.',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now->copy()->subHours(1),
                'updated_at' => $now->copy()->subHours(1),
            ],
            [
                'id' => '11111111-1111-1111-1111-111111111005',
                'notifiable_type' => $notifiableType,
                'notifiable_id' => 'AD_001',
                'type' => 'App\\Notifications\\NewCandidateApplicationNotification',
                'data' => json_encode([
                    'applied_at' => $now->toDateString(),
                    'message' => 'Ứng viên Phạm Gia Hân (user2@gmail.com) vừa nộp hồ sơ cho vị trí "Frontend Developer React".',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now->copy()->subMinutes(45),
                'updated_at' => $now->copy()->subMinutes(45),
            ],
            [
                'id' => '11111111-1111-1111-1111-111111111006',
                'notifiable_type' => $notifiableType,
                'notifiable_id' => 'ST_001',
                'type' => 'App\\Notifications\\NewCandidateApplicationNotification',
                'data' => json_encode([
                    'applied_at' => $now->toDateString(),
                    'message' => 'Ứng viên Trịnh Minh Tuấn (user6@gmail.com) vừa nộp hồ sơ cho vị trí "Lập Trình Viên PHP/Laravel".',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now->copy()->subMinutes(30),
                'updated_at' => $now->copy()->subMinutes(30),
            ],
        ];

        foreach ($samples as $sample) {
            DB::table('notifications')->updateOrInsert(
                ['id' => $sample['id']],
                $sample
            );
        }
    }
}
