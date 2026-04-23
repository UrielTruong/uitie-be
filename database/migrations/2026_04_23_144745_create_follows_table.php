<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            // NO ACTION (not CASCADE): MSSQL blocks — two direct FKs from USERS to FOLLOWS
            $table->foreignId('following_id')->constrained('users');
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice

            $table->unique(['follower_id', 'following_id']); // SQL: CONSTRAINT UQ_FOLLOW
        });

        // SQL: ALTER TABLE FOLLOW ADD CONSTRAINT CK_FOLLOW_NOT_SELF CHECK (follower_id <> following_id)
        DB::statement('ALTER TABLE follows ADD CONSTRAINT CK_follows_not_self CHECK (follower_id <> following_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
