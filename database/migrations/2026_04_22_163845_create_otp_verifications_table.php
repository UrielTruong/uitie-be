<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_verification', function (Blueprint $table) {
            // Khóa chính (MSSQL tự hiểu là IDENTITY(1,1))
            $table->id('otp_id');

            // Khóa ngoại tham chiếu đến cột 'id' của bảng 'users'
            // Việc dùng constrained() yêu cầu bảng users phải được tạo trước
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('otp_code', 10);

            // Laravel giả lập Enum cho MSSQL qua NVARCHAR + CHECK constraint
            $table->string('otp_type', 30);

            $table->dateTime('expired_at')->nullable();
            $table->boolean('is_used')->default(false);

            // Laravel Timestamps (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verification');
    }
};
