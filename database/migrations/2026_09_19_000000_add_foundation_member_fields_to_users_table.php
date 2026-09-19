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
            // Identitas Kependudukan & Pribadi (Yayasan)
            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 20)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('nik'); // 'Laki-laki' / 'Perempuan'
            }
            if (!Schema::hasColumn('users', 'birth_place')) {
                $table->string('birth_place', 100)->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('birth_place');
            }
            if (!Schema::hasColumn('users', 'religion')) {
                $table->string('religion', 50)->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 50)->nullable()->after('religion');
            }
            if (!Schema::hasColumn('users', 'last_education')) {
                $table->string('last_education', 50)->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('last_education');
            }

            // Alamat Domisili Lengkap
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('province');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'village')) {
                $table->string('village', 100)->nullable()->after('district');
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('village');
            }

            // Data Ahli Waris / Kontak Darurat Yayasan
            if (!Schema::hasColumn('users', 'beneficiary_name')) {
                $table->string('beneficiary_name', 100)->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'beneficiary_relation')) {
                $table->string('beneficiary_relation', 50)->nullable()->after('beneficiary_name');
            }
            if (!Schema::hasColumn('users', 'beneficiary_phone')) {
                $table->string('beneficiary_phone', 25)->nullable()->after('beneficiary_relation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'nik', 'gender', 'birth_place', 'birth_date', 'religion',
                'marital_status', 'last_education', 'occupation',
                'address', 'province', 'city', 'district', 'village', 'postal_code',
                'beneficiary_name', 'beneficiary_relation', 'beneficiary_phone'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
