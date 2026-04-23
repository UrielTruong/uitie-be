<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            // NO ACTION (not CASCADE): MSSQL blocks — two paths reach COMMENTS from USERS:
            //   USERS → POSTS → COMMENTS (via post_id cascade)
            //   USERS → COMMENTS (direct via user_id)
            $table->foreignId('user_id')->constrained('users');
            // Self-referencing FK; no ON DELETE (RESTRICT by default — prevents orphan replies)
            $table->unsignedBigInteger('parent_comment_id')->nullable();
            $table->text('content')->nullable();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice

            $table->foreign('parent_comment_id')->references('id')->on('comments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
