<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Xóa foreign key cũ
            $table->dropForeign(['job_id']);
            
            // Thêm foreign key mới với cascadeOnDelete
            $table->foreign('job_id')->references('job_id')->on('job_postings')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Revert lại nullOnDelete
            $table->dropForeign(['job_id']);
            $table->foreign('job_id')->references('job_id')->on('job_postings')->nullOnDelete();
        });
    }
};
