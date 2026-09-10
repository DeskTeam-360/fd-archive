<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_companies', function (Blueprint $table) {
            $table->unsignedBigInteger('fd_id')->primary(); // Freshdesk company ID
            $table->string('name');
            $table->string('domains')->nullable();          // comma-separated domains
            $table->text('description')->nullable();
            $table->string('note')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('fd_created_at')->nullable();
            $table->timestamp('fd_updated_at')->nullable();
            $table->timestamps();                          // local record timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_companies');
    }
};
