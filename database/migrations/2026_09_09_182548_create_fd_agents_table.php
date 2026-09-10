<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_agents', function (Blueprint $table) {
            $table->unsignedBigInteger('fd_id')->primary();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('job_title')->nullable();
            $table->boolean('occasional')->default(false);
            $table->boolean('active')->default(true);
            $table->string('type')->nullable(); // agent | supervisor | admin
            $table->json('group_ids')->nullable();
            $table->json('role_ids')->nullable();
            $table->json('skill_ids')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('fd_created_at')->nullable();
            $table->timestamp('fd_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_agents');
    }
};
