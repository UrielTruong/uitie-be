<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            // NO ACTION (not CASCADE): MSSQL blocks — two paths reach LIKES from USERS:
            //   USERS → POSTS → LIKES (via post_id cascade)
            //   USERS → LIKES (direct via user_id)
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice

            $table->unique(['user_id', 'post_id']); // SQL: CONSTRAINT UQ_LIKES
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
