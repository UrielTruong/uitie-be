<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQL: composite PK (post_id, attachment_id); added id() for Eloquent compatibility
        Schema::create('post_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('attachment_id')->constrained('attachments')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'attachment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_attachments');
    }
};
