<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // WARNING: Table 'notifications' conflicts with Laravel's Notifiable trait on User model.
    // $user->notifications (from Notifiable) targets this table but expects DatabaseNotification schema.
    // Fix: remove Notifiable from User, rename table to 'user_notifications', or override the relationship.
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('type', 50)->nullable();
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice

            // SQL: CREATE INDEX idx_notification_user / idx_notification_read
            $table->index('user_id', 'idx_notifications_user');
            $table->index('is_read', 'idx_notifications_read');
        });

        DB::statement("ALTER TABLE notifications ADD CONSTRAINT CK_notifications_type CHECK (type IN ('POST_APPROVED','POST_REJECTED','NEW_LIKE','NEW_COMMENT','NEW_FOLLOWER','GROUP_INVITE','SYSTEM_ALERT'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
