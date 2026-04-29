<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->text('file_url');
            $table->string('file_type', 20)->nullable();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice
        });

        DB::statement("ALTER TABLE attachments ADD CONSTRAINT CK_attachments_file_type CHECK (file_type IN ('Image','Video','Document'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
