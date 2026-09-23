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
            if (!Schema::hasColumn('users', 'is_left_handed')) {
                $table->string('is_left_handed', 10)->nullable()->default('Tidak')->after('phone');
            }
            if (!Schema::hasColumn('users', 'beneficiary_birth_date')) {
                $table->date('beneficiary_birth_date')->nullable()->after('beneficiary_name');
            }
            if (!Schema::hasColumn('users', 'emergency_phone')) {
                $table->string('emergency_phone', 25)->nullable()->after('beneficiary_relation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['is_left_handed', 'beneficiary_birth_date', 'emergency_phone'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
