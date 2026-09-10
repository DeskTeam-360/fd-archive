<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('fd_id')->primary(); // Freshdesk ticket ID
            $table->unsignedBigInteger('fd_company_id')->nullable()->index();
            $table->unsignedBigInteger('fd_requester_id')->nullable()->index();
            $table->unsignedBigInteger('fd_responder_id')->nullable()->index(); // assigned agent
            $table->string('subject');
            $table->longText('description')->nullable();      // HTML body
            $table->text('description_text')->nullable();     // plain text
            $table->unsignedTinyInteger('status');            // 2=open,3=pending,4=resolved,5=closed
            $table->string('status_label')->nullable();       // human-readable
            $table->unsignedTinyInteger('priority');          // 1=low,2=medium,3=high,4=urgent
            $table->string('priority_label')->nullable();
            $table->string('type')->nullable();
            $table->string('source')->nullable();             // channel
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('due_by')->nullable();
            $table->timestamp('fr_due_by')->nullable();       // first response due
            $table->timestamp('fd_created_at')->nullable();
            $table->timestamp('fd_updated_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('fd_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_tickets');
    }
};
