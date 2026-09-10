<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fd_tickets', function (Blueprint $table) {
            $table->timestamp('comments_imported_at')->nullable()->after('fd_updated_at');
        });

        // Semua ticket yang ada sekarang dianggap sudah punya comments
        DB::table('fd_tickets')->update(['comments_imported_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('fd_tickets', function (Blueprint $table) {
            $table->dropColumn('comments_imported_at');
        });
    }
};
