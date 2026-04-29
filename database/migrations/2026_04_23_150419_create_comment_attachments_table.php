<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQL: composite PK (comment_id, attachment_id); added id() for Eloquent compatibility
        Schema::create('comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['comment_id', 'attachment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_attachments');
    }
};
