<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('group_chats')->cascadeOnDelete();
            // NO ACTION (not CASCADE): MSSQL blocks — two paths reach GROUP_MEMBERS from USERS:
            //   USERS → GROUP_CHATS (SET NULL on created_by) → GROUP_MEMBERS (CASCADE on group_id)
            //   USERS → GROUP_MEMBERS (direct via user_id)
            $table->foreignId('user_id')->constrained('users');
            $table->string('status', 20)->default('Pending');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps(); // SQL: no timestamps on this table; added for best practice

            $table->unique(['group_id', 'user_id']); // SQL: CONSTRAINT UQ_GROUP_MEMBER
        });

        DB::statement("ALTER TABLE group_members ADD CONSTRAINT CK_group_members_status CHECK (status IN ('Pending','Accepted','Rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
