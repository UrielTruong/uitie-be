<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_chats', function (Blueprint $table) {
            $table->id();
            $table->string('group_name')->nullable();
            // SET NULL: keep group history when creator is deleted (matches SQL)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_chats');
    }
};
