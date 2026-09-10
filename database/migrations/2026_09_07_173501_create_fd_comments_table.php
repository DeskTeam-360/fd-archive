<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('fd_id')->primary(); // Freshdesk conversation ID
            $table->unsignedBigInteger('fd_ticket_id')->index();
            $table->unsignedBigInteger('fd_user_id')->nullable()->index(); // agent/contact who wrote it
            $table->longText('body')->nullable();             // HTML
            $table->text('body_text')->nullable();            // plain text
            $table->boolean('incoming')->default(false);      // true = from customer
            $table->boolean('private')->default(false);       // true = internal note
            $table->string('from_email')->nullable();
            $table->json('to_emails')->nullable();
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->json('attachments')->nullable();          // array of {name, url}
            $table->timestamp('fd_created_at')->nullable();
            $table->timestamp('fd_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_comments');
    }
};
