<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'employee_code')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('employee_code')->nullable()->after('user_id')->unique();
            });
        }

        if (!Schema::hasTable('employees') || !Schema::hasColumn('employees', 'employee_code')) {
            return;
        }

        $maxNum = (int) DB::table('employees')
            ->where('user_id', 'like', 'ST_%')
            ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(user_id, 4) AS UNSIGNED)), 0) as max_num")
            ->value('max_num');

        $employees = DB::table('employees')
            ->select('user_id', 'employee_code')
            ->orderBy('created_at')
            ->orderBy('user_id')
            ->get();

        foreach ($employees as $employee) {
            if (!empty($employee->employee_code)) {
                continue;
            }

            $code = null;
            if (preg_match('/^ST_(\d+)$/', (string) $employee->user_id)) {
                $code = (string) $employee->user_id;
            } else {
                do {
                    $maxNum++;
                    $code = sprintf('ST_%03d', $maxNum);
                } while (DB::table('employees')->where('employee_code', $code)->exists());
            }

            DB::table('employees')
                ->where('user_id', $employee->user_id)
                ->update(['employee_code' => $code]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'employee_code')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropUnique(['employee_code']);
                $table->dropColumn('employee_code');
            });
        }
    }
};
