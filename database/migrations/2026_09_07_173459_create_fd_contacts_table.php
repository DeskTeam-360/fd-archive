<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('fd_id')->primary(); // Freshdesk contact ID
            $table->unsignedBigInteger('fd_company_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('job_title')->nullable();
            $table->string('language')->nullable();
            $table->string('time_zone')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('fd_created_at')->nullable();
            $table->timestamp('fd_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_contacts');
    }
};
