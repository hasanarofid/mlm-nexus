<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_premier')) {
                $table->boolean('is_premier')->default(false)->after('package_name');
                $table->timestamp('premier_activated_at')->nullable()->after('is_premier');
                $table->timestamp('premier_expires_at')->nullable()->after('premier_activated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_premier')) {
                $table->dropColumn(['is_premier', 'premier_activated_at', 'premier_expires_at']);
            }
        });
    }
};
