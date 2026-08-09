<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recruitment_periods') || !Schema::hasColumn('recruitment_periods', 'status')) {
            return;
        }

        DB::table('recruitment_periods')
            ->where('status', 'draft')
            ->update(['status' => 'closed']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('recruitment_periods') || !Schema::hasColumn('recruitment_periods', 'status')) {
            return;
        }
    }
};
