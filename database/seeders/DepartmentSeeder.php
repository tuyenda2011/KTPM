<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Department::updateOrCreate(
            ['department_id' => 'DEPT001'],
            [
                'name' => 'Phòng Nhân Sự',
                'description' => 'Quản lý nhân sự, tuyển dụng và đào tạo',

            ]
        );

        \App\Models\Department::updateOrCreate(
            ['department_id' => 'DEPT002'],
            [
                'name' => 'Phòng Kế Toán',
                'description' => 'Quản lý tài chính và kế toán',

            ]
        );

        \App\Models\Department::updateOrCreate(
            ['department_id' => 'DEPT003'],
            [
                'name' => 'Phòng IT',
                'description' => 'Công nghệ thông tin và hệ thống',

            ]
        );

        \App\Models\Department::updateOrCreate(
            ['department_id' => 'DEPT004'],
            [
                'name' => 'Phòng Marketing',
                'description' => 'Kinh doanh và tiếp thị',

            ]
        );
    }
}