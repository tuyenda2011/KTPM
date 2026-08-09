<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentManagerSeeder extends Seeder
{
    public function run(): void
    {
        Department::where('department_id', 'DEPT001')->update([
            'manager_user_id' => 'ST_001',
        ]);

        Department::where('department_id', 'DEPT002')->update([
            'manager_user_id' => 'ST_002',
        ]);

        Department::where('department_id', 'DEPT003')->update([
            'manager_user_id' => 'ST_003',
        ]);

        Department::where('department_id', 'DEPT004')->update([
            'manager_user_id' => 'ST_008',
        ]);
    }
}