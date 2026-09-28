<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update existing users with basic / null / old names to Standard
        DB::table('users')
            ->whereNull('package_name')
            ->orWhereIn('package_name', ['Basic', 'basic', '', 'Starter', 'Seller'])
            ->update(['package_name' => 'Standard']);

        // 2. Update existing vouchers with basic / null to Standard
        if (Schema::hasTable('vouchers')) {
            DB::table('vouchers')
                ->whereNull('package_name')
                ->orWhereIn('package_name', ['Basic', 'basic', ''])
                ->update(['package_name' => 'Standard']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as Standard is now canonical default
    }
};
