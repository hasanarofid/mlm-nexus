<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bonus_logs MODIFY COLUMN category ENUM(
                'sponsor',
                'pasangan',
                'titik',
                'reward',
                'penarikan',
                'po',
                'pal',
                'ro',
                'ro_matching',
                'tpr',
                'tier',
                'generasi'
            ) NOT NULL DEFAULT 'sponsor'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bonus_logs MODIFY COLUMN category ENUM(
                'sponsor',
                'pasangan',
                'titik',
                'reward',
                'penarikan',
                'po',
                'pal',
                'ro',
                'tpr',
                'tier'
            ) NOT NULL DEFAULT 'sponsor'");
        }
    }
};
