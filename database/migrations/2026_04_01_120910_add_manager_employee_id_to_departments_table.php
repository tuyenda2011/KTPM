<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'manager_user_id')) {
                $table->string('manager_user_id')->nullable()->unique()->after('description');

                $table->foreign('manager_user_id')
                    ->references('user_id')
                    ->on('employees')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'manager_user_id')) {
                $table->dropForeign(['manager_user_id']);
                $table->dropColumn('manager_user_id');
            }
        });
    }
};