<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('degree')->nullable()->after('education_level');
            $table->string('school_name')->nullable()->after('degree');
            $table->text('certificates')->nullable()->after('school_name');
            $table->text('language_certificates')->nullable()->after('certificates');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'degree',
                'school_name',
                'certificates',
                'language_certificates',
            ]);
        });
    }
};