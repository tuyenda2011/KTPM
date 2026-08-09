<?php

namespace Database\Seeders;

use App\Models\RecruitmentPeriod;
use Illuminate\Database\Seeder;

class RecruitmentPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [
                'period_id' => 'RP_001',
                'name' => 'Kỳ tuyển dụng Quý 1/2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-03-31',
                'status' => 'closed',
                'notes' => 'Kỳ tuyển dụng đầu năm, tập trung các vị trí vận hành cốt lõi.',
            ],
            [
                'period_id' => 'RP_002',
                'name' => 'Kỳ tuyển dụng Quý 2/2026',
                'start_date' => '2026-04-01',
                'end_date' => '2026-06-30',
                'status' => 'open',
                'notes' => 'Kỳ tuyển dụng đang mở cho nhiều phòng ban.',
            ],
            [
                'period_id' => 'RP_003',
                'name' => 'Kỳ tuyển dụng Quý 3/2026',
                'start_date' => '2026-07-01',
                'end_date' => '2026-09-30',
                'status' => 'closed',
                'notes' => 'Kỳ tuyển dụng cho giai đoạn mở rộng giữa năm.',
            ],
            [
                'period_id' => 'RP_004',
                'name' => 'Kỳ tuyển dụng Quý 4/2026',
                'start_date' => '2026-10-01',
                'end_date' => '2026-12-31',
                'status' => 'closed',
                'notes' => 'Kỳ tuyển dụng cuối năm cho kế hoạch nhân sự năm sau.',
            ],
        ];

        foreach ($periods as $period) {
            RecruitmentPeriod::updateOrCreate(
                ['period_id' => $period['period_id']],
                $period
            );
        }
    }
}