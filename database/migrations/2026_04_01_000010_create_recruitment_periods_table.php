<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recruitment_periods')) {
            Schema::create('recruitment_periods', function (Blueprint $table) {
                $table->string('period_id')->primary();
                $table->string('name');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['draft', 'open', 'closed'])->default('draft');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('job_postings') && !Schema::hasColumn('job_postings', 'recruitment_period_id')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->string('recruitment_period_id')->nullable()->after('job_id');
                $table->foreign('recruitment_period_id')
                    ->references('period_id')
                    ->on('recruitment_periods')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('job_postings') && Schema::hasColumn('job_postings', 'recruitment_period_id')) {
            $defaultPeriodId = DB::table('recruitment_periods')
                ->orderBy('created_at')
                ->value('period_id');

            if (!$defaultPeriodId) {
                $defaultPeriodId = 'RP_001';
                while (DB::table('recruitment_periods')->where('period_id', $defaultPeriodId)->exists()) {
                    $number = (int) substr($defaultPeriodId, 3) + 1;
                    $defaultPeriodId = 'RP_' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                }

                $now = Carbon::now();
                DB::table('recruitment_periods')->insert([
                    'period_id' => $defaultPeriodId,
                    'name' => 'Kỳ tuyển dụng hiện tại',
                    'start_date' => $now->copy()->startOfMonth()->toDateString(),
                    'end_date' => $now->copy()->endOfYear()->toDateString(),
                    'status' => 'open',
                    'notes' => 'Kỳ mặc định được tạo tự động khi nâng cấp hệ thống.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('job_postings')
                ->whereNull('recruitment_period_id')
                ->update(['recruitment_period_id' => $defaultPeriodId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_postings') && Schema::hasColumn('job_postings', 'recruitment_period_id')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->dropForeign(['recruitment_period_id']);
            });

            Schema::table('job_postings', function (Blueprint $table) {
                $table->dropColumn('recruitment_period_id');
            });
        }

        Schema::dropIfExists('recruitment_periods');
    }
};
