<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Sử dụng id() mặc định (Sẽ tạo cột 'id' kiểu BIGINT IDENTITY)
            $table->id();

            // Basic Info
            $table->string('email')->unique();
            $table->string('password');
            $table->string('full_name');

            // Student Info
            $table->string('mssv', 20)->nullable();
            $table->string('phone_number', 20)->nullable();

            // Role & Status (MSSQL dùng String + Logic Const trong Model)
            $table->string('role')->default('Student');
            $table->string('status')->default('Inactive');

            $table->text('status_reason')->nullable();

            // Academic Info
            $table->string('faculty')->nullable();
            $table->string('class_name', 100)->nullable();
            $table->string('academic_year', 20)->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        /**
         * Filtered Index cho MSSQL:
         * Cho phép nhiều dòng có MSSV là NULL, nhưng nếu có giá trị thì phải Unique.
         */
        DB::statement("
            CREATE UNIQUE NONCLUSTERED INDEX UX_users_mssv_not_null
            ON users(mssv)
            WHERE mssv IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
