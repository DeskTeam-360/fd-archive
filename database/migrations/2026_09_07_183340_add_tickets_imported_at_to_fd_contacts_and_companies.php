<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fd_contacts', function (Blueprint $table) {
            $table->timestamp('tickets_imported_at')->nullable()->after('fd_updated_at');
            $table->timestamp('last_synced_at')->nullable()->after('tickets_imported_at');
        });

        Schema::table('fd_companies', function (Blueprint $table) {
            $table->timestamp('tickets_imported_at')->nullable()->after('fd_updated_at');
            $table->timestamp('last_synced_at')->nullable()->after('tickets_imported_at');
        });
    }

    public function down(): void
    {
        Schema::table('fd_contacts', function (Blueprint $table) {
            $table->dropColumn(['tickets_imported_at', 'last_synced_at']);
        });

        Schema::table('fd_companies', function (Blueprint $table) {
            $table->dropColumn(['tickets_imported_at', 'last_synced_at']);
        });
    }
};
