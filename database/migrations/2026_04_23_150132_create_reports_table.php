<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            // All user FKs: NO CASCADE to avoid MSSQL multiple cascade paths (SQL comment: same reason)
            $table->foreignId('reporter_id')->constrained('users');
            $table->foreignId('reported_user_id')->nullable()->constrained('users');
            $table->foreignId('reported_post_id')->nullable()->constrained('posts');
            // WARNING: SET NULL on resolved_by conflicts with CK_reports_status_logic
            // (status=Resolved requires resolved_by IS NOT NULL; SET NULL would violate it)
            // See progress.md. Change to NO ACTION if this causes issues.
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('Pending');
            $table->string('target_type', 20)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps(); // SQL: created_at only; added updated_at for best practice
        });

        DB::statement("ALTER TABLE reports ADD CONSTRAINT CK_reports_status CHECK (status IN ('Pending','Resolved','Dismissed'))");
        DB::statement("ALTER TABLE reports ADD CONSTRAINT CK_reports_target_type CHECK (target_type IN ('User','Post'))");
        DB::statement("ALTER TABLE reports ADD CONSTRAINT CK_reports_target CHECK ((target_type = 'User' AND reported_user_id IS NOT NULL AND reported_post_id IS NULL) OR (target_type = 'Post' AND reported_post_id IS NOT NULL AND reported_user_id IS NULL))");
        DB::statement("ALTER TABLE reports ADD CONSTRAINT CK_reports_status_logic CHECK ((status = 'Pending' AND resolved_by IS NULL AND resolved_at IS NULL) OR (status IN ('Resolved','Dismissed') AND resolved_by IS NOT NULL AND resolved_at IS NOT NULL))");
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
