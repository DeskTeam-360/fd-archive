<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fd_companies', function (Blueprint $table) {
            $table->text('note')->nullable()->change();
            $table->text('domains')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fd_companies', function (Blueprint $table) {
            $table->string('note')->nullable()->change();
            $table->string('domains')->nullable()->change();
        });
    }
};
