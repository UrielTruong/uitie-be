<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // NO ACTION (not CASCADE): MSSQL blocks — two paths reach MESSAGES from USERS:
            //   USERS → GROUP_CHATS (SET NULL on created_by) → MESSAGES (CASCADE on group_id)
            //   USERS → MESSAGES (direct via sender_id / receiver_id)
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->nullable()->constrained('users');
            $table->foreignId('group_id')->nullable()->constrained('group_chats')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice
        });

        // SQL: CONSTRAINT CK_MESSAGE_TARGET
        DB::statement('ALTER TABLE messages ADD CONSTRAINT CK_messages_target CHECK ((receiver_id IS NOT NULL AND group_id IS NULL) OR (receiver_id IS NULL AND group_id IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
