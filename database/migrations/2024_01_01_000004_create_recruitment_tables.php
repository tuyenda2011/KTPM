<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->string('department_id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('manager_id')->nullable();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->string('user_id')->primary(); // Kế thừa từ users
            $table->string('position')->nullable(); // Chức vụ
            $table->string('identity_card')->nullable()->unique(); // CCCD
            $table->enum('marital_status', ['Độc thân', 'Đã kết hôn', 'Ly hôn'])->nullable();
            $table->string('hometown')->nullable();
            $table->string('current_address')->nullable();
            $table->date('start_date')->nullable(); // Ngày làm việc
            $table->string('department_id')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->default('Việt Nam');
            $table->string('avatar_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('contract_path')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Đang làm', 'Nghỉ việc', 'Tạm nghỉ'])->default('Đang làm');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('department_id')->references('department_id')->on('departments')->nullOnDelete();
        });

        Schema::create('job_postings', function (Blueprint $table) {
            $table->string('job_id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable();
            $table->string('department')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', ['active', 'filled', 'closed'])->default('active');
            $table->timestamps();
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->string('user_id')->primary(); // Kế thừa từ users
            $table->string('job_id')->nullable();
            $table->string('position_applied')->nullable();
            $table->text('experience')->nullable();
            $table->text('education')->nullable();
            $table->enum('status', ['Đang chờ', 'Đã duyệt CV', 'Phỏng vấn', 'Đã nhận việc', 'Từ chối'])->default('Đang chờ');
            $table->text('notes')->nullable();
            $table->date('applied_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('job_id')->references('job_id')->on('job_postings')->nullOnDelete();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->string('interview_id')->primary();
            $table->string('user_id'); // Kế thừa từ candidates/users
            $table->dateTime('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('result', ['pass', 'fail', 'pending'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('candidates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
