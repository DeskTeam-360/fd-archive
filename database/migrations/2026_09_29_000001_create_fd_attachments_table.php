<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('fd_id')->primary();
            $table->unsignedBigInteger('fd_ticket_id')->index();
            $table->unsignedBigInteger('fd_comment_id')->nullable()->index();
            $table->string('name', 500);
            $table->string('content_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('disk', 50)->default('local');
            $table->string('path', 1000)->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::table('fd_tickets', function (Blueprint $table) {
            $table->timestamp('attachments_imported_at')->nullable()->after('comments_imported_at');
        });
    }

    public function down(): void
    {
        Schema::table('fd_tickets', function (Blueprint $table) {
            $table->dropColumn('attachments_imported_at');
        });
        Schema::dropIfExists('fd_attachments');
    }
};
