<?php

namespace Database\Seeders;

use App\Models\Interview;
use Illuminate\Database\Seeder;

class InterviewSeeder extends Seeder
{
    public function run(): void
    {
        $interviews = [
            ['user_id' => 'US_002', 'scheduled_at' => '2024-01-20 10:00:00', 'result' => 'pass', 'notes' => 'Phỏng vấn kỹ thuật pass'],
            ['user_id' => 'US_003', 'scheduled_at' => '2024-01-22 14:00:00', 'result' => 'pass', 'notes' => 'Vòng phỏng vấn cuối pass'],
            ['user_id' => 'US_004', 'scheduled_at' => '2024-01-18 09:00:00', 'result' => 'fail', 'notes' => 'Kiến thức cơ bản còn yếu'],
            ['user_id' => 'US_007', 'scheduled_at' => '2024-01-25 11:00:00', 'result' => 'pending', 'notes' => 'Đang chờ kết quả'],
            ['user_id' => 'US_008', 'scheduled_at' => '2024-01-28 15:00:00', 'result' => 'pass', 'notes' => 'Kinh nghiệm tốt, tư duy tốt'],
            ['user_id' => 'US_010', 'scheduled_at' => '2024-01-16 10:30:00', 'result' => 'pass', 'notes' => 'Giao tiếp tốt, hiểu rõ quy trình'],
            ['user_id' => 'US_011', 'scheduled_at' => '2024-01-26 13:00:00', 'result' => 'pending', 'notes' => 'Chờ kết quả vòng 2'],
            ['user_id' => 'US_014', 'scheduled_at' => '2024-01-27 09:30:00', 'result' => 'pending', 'notes' => 'Đang trong quá trình đánh giá'],
            ['user_id' => 'US_015', 'scheduled_at' => '2024-01-19 14:00:00', 'result' => 'fail', 'notes' => 'Kiến thức chuyên môn còn hạn chế'],
        ];

        foreach ($interviews as $item) {
            Interview::updateOrCreate(
                [
                    'user_id' => $item['user_id'],
                    'scheduled_at' => $item['scheduled_at'],
                ],
                [
                    'result' => $item['result'],
                    'notes' => $item['notes'],
                ]
            );
        }
    }
}