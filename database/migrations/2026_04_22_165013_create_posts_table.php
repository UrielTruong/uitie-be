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
        Schema::create('posts', function (Blueprint $table) {
            // Khóa chính mặc định 'id'
            $table->id();

            // Khóa ngoại tham chiếu bảng Users
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Khóa ngoại tham chiếu bảng Categories
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('set null');

            // Quan hệ phân cấp (Self-referencing) cho bài viết cha/con
            $table->unsignedBigInteger('parent_post_id')->nullable();
            $table->foreign('parent_post_id')
                ->references('id')
                ->on('posts')
                ->onDelete('no action'); // MSSQL giới hạn cascade trên chính nó

            $table->text('content')->nullable();

            // Visibility & Status (Dùng String + Constant ở Model)
            $table->string('visibility', 20)->default('Public');
            $table->string('status', 20)->default('Pending');

            $table->text('reject_reason')->nullable();
            $table->boolean('is_edited')->default(false);

            // Laravel Timestamps & Soft Deletes
            $table->timestamps();
            $table->softDeletes(); // Tạo cột 'deleted_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
