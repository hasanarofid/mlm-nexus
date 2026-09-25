<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles and Permissions
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(ProductSeeder::class);

        // 2. Seed Default Users and Assign Roles
        $admin = User::where('username', 'admin')->orWhere('email', 'admin@nexuscommunity.com')->first() ?: new User();
        $admin->fill([
            'name' => 'President Director (Admin)',
            'username' => 'admin',
            'email' => 'admin@nexuscommunity.com',
            'password' => bcrypt('password'),
            'left_count' => 3,
            'right_count' => 2,
            'left_points' => 1,
            'right_points' => 0,
            'package_name' => 'Standard',
            'saldo' => 500000.00,
            'total_bonus' => 500000.00,
        ]);
        $admin->save();
        $admin->assignRole('admin');

        // Level 2 (Children of Admin)
        $budi = User::where('username', 'budi')->orWhere('email', 'budi@nexuscommunity.com')->first() ?: new User();
        $budi->fill([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'email' => 'budi@nexuscommunity.com',
            'password' => bcrypt('password'),
            'parent_id' => $admin->id,
            'position' => 'left',
            'left_count' => 1,
            'right_count' => 1,
            'left_points' => 0,
            'right_points' => 0,
            'package_name' => 'Standard',
            'saldo' => 500000.00,
            'total_bonus' => 500000.00,
        ]);
        $budi->save();
        $budi->assignRole('client');

        $siti = User::where('username', 'siti')->orWhere('email', 'siti@nexuscommunity.com')->first() ?: new User();
        $siti->fill([
            'name' => 'Siti Rahma',
            'username' => 'siti',
            'email' => 'siti@nexuscommunity.com',
            'password' => bcrypt('password'),
            'parent_id' => $admin->id,
            'position' => 'right',
            'left_count' => 1,
            'right_count' => 0,
            'left_points' => 0,
            'right_points' => 0,
            'package_name' => 'Standard',
        ]);
        $siti->save();
        $siti->assignRole('client');

        // Level 3 (Grandchildren)
        $dewi = User::where('username', 'dewi')->orWhere('email', 'dewi@nexuscommunity.com')->first() ?: new User();
        $dewi->fill([
            'name' => 'Dewi Lestari',
            'username' => 'dewi',
            'email' => 'dewi@nexuscommunity.com',
            'password' => bcrypt('password'),
            'parent_id' => $budi->id,
            'position' => 'left',
            'left_count' => 0,
            'right_count' => 0,
            'left_points' => 0,
            'right_points' => 0,
            'package_name' => 'Standard',
        ]);
        $dewi->save();
        $dewi->assignRole('client');

        $eko = User::where('username', 'eko')->orWhere('email', 'eko@nexuscommunity.com')->first() ?: new User();
        $eko->fill([
            'name' => 'Eko Prasetyo',
            'username' => 'eko',
            'email' => 'eko@nexuscommunity.com',
            'password' => bcrypt('password'),
            'parent_id' => $budi->id,
            'position' => 'right',
            'left_count' => 0,
            'right_count' => 0,
            'left_points' => 0,
            'right_points' => 0,
            'package_name' => 'Standard',
        ]);
        $eko->save();
        $eko->assignRole('client');

        $fajar = User::where('username', 'fajar')->orWhere('email', 'fajar@nexuscommunity.com')->first() ?: new User();
        $fajar->fill([
            'name' => 'Fajar Hidayat',
            'username' => 'fajar',
            'email' => 'fajar@nexuscommunity.com',
            'password' => bcrypt('password'),
            'parent_id' => $siti->id,
            'position' => 'left',
            'left_count' => 0,
            'right_count' => 0,
            'left_points' => 0,
            'right_points' => 0,
            'package_name' => 'Standard',
        ]);
        $fajar->save();
        $fajar->assignRole('client');

        // 2b. Seed Active & Used Vouchers for Admin matching mockup
        $v1 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-9812-XYZ'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(5),
            ]
        );

        $v2 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-4432-ABC'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(5),
            ]
        );

        \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-1234-MNO'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'used',
                'used_by_id' => $budi->id,
                'used_at' => now()->subDays(4),
                'created_at' => now()->subDays(5),
            ]
        );

        \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-5678-PQR'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'used',
                'used_by_id' => $siti->id,
                'used_at' => now()->subDays(3),
                'created_at' => now()->subDays(5),
            ]
        );

        \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-2222-BBB'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'used',
                'used_by_id' => $dewi->id,
                'used_at' => now()->subDays(1),
                'created_at' => now()->subDays(3),
            ]
        );

        // Seed Transfer History matching mockup
        $vt1 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-5555-DDD'],
            [
                'user_id' => $budi->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(2),
            ]
        );

        \App\Models\VoucherTransfer::updateOrCreate(
            ['voucher_code' => 'PIN-5555-DDD'],
            [
                'voucher_id' => $vt1->id,
                'sender_id' => $admin->id,
                'recipient_id' => $budi->id,
                'created_at' => now()->subDays(2)->setHour(21)->setMinute(20),
            ]
        );

        $vt2 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-8888-EEE'],
            [
                'user_id' => $siti->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(1),
            ]
        );

        \App\Models\VoucherTransfer::updateOrCreate(
            ['voucher_code' => 'PIN-8888-EEE'],
            [
                'voucher_id' => $vt2->id,
                'sender_id' => $admin->id,
                'recipient_id' => $siti->id,
                'created_at' => now()->subDays(1)->setHour(23)->setMinute(0),
            ]
        );

        // Seed Financial Wallet Transactions matching mockup
        \App\Models\WalletTransaction::updateOrCreate(
            ['description' => 'Transfer saldo modal awal', 'user_id' => $admin->id],
            [
                'type' => 'out',
                'category' => 'transfer',
                'amount' => 150000,
                'related_user_id' => $budi->id,
                'created_at' => now()->subDays(3)->setHour(10)->setMinute(15)->setSecond(0),
            ]
        );

        \App\Models\WalletTransaction::updateOrCreate(
            ['description' => 'Bonus Sponsor: Pendaftaran Mitra Dewi', 'user_id' => $budi->id],
            [
                'type' => 'in',
                'category' => 'sponsor',
                'amount' => 250000,
                'related_user_id' => $dewi->id,
                'created_at' => now()->subDays(2)->setHour(10)->setMinute(0)->setSecond(0),
            ]
        );

        \App\Models\WalletTransaction::updateOrCreate(
            ['description' => 'Bonus Sponsor: Pendaftaran Mitra Eko', 'user_id' => $budi->id],
            [
                'type' => 'in',
                'category' => 'sponsor',
                'amount' => 250000,
                'related_user_id' => $eko->id,
                'created_at' => now()->subDays(1)->setHour(14)->setMinute(0)->setSecond(0),
            ]
        );

        \App\Models\WalletTransaction::updateOrCreate(
            ['description' => 'Cairkan bonus sponsor ke E-Wallet', 'user_id' => $admin->id],
            [
                'type' => 'in',
                'category' => 'payout',
                'amount' => 300000,
                'created_at' => now()->subDays(2)->setHour(14)->setMinute(30)->setSecond(0),
            ]
        );

        // Seed Activity Bonus Logs matching mockup
        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B001'],
            [
                'user_id' => $admin->id,
                'category' => 'sponsor',
                'source_user_id' => $budi->id,
                'description' => 'Bonus Sponsor: Pendaftaran budi (USR002)',
                'amount' => 250000,
                'created_at' => now()->subDays(4)->setHour(18)->setMinute(30)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B002'],
            [
                'user_id' => $admin->id,
                'category' => 'sponsor',
                'source_user_id' => $siti->id,
                'description' => 'Bonus Sponsor: Pendaftaran siti (USR003)',
                'amount' => 250000,
                'created_at' => now()->subDays(3)->setHour(16)->setMinute(15)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B003'],
            [
                'user_id' => $budi->id,
                'category' => 'sponsor',
                'source_user_id' => $dewi->id,
                'description' => 'Bonus Sponsor: Pendaftaran dewi (dewi)',
                'amount' => 250000,
                'created_at' => now()->subDays(2)->setHour(10)->setMinute(0)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B004'],
            [
                'user_id' => $budi->id,
                'category' => 'sponsor',
                'source_user_id' => $eko->id,
                'description' => 'Bonus Sponsor: Pendaftaran eko (eko)',
                'amount' => 250000,
                'created_at' => now()->subDays(1)->setHour(14)->setMinute(0)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B005'],
            [
                'user_id' => $admin->id,
                'category' => 'sponsor',
                'source_user_id' => $eko->id,
                'description' => 'Bonus Sponsor: Pendaftaran eko (USR005)',
                'amount' => 25000,
                'created_at' => now()->subDays(1)->setHour(23)->setMinute(0)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'P001'],
            [
                'user_id' => $admin->id,
                'category' => 'generasi',
                'source_user_id' => $budi->id,
                'description' => 'Bonus Generasi: Tim Jaringan Level 1',
                'amount' => 250000,
                'created_at' => now()->subDays(2)->setHour(12)->setMinute(0)->setSecond(0),
            ]
        );

        // 3. Seed Settings
        $this->call(SettingSeeder::class);

        // 4. Seed Pages and Sections
        $this->call(PageAndSectionSeeder::class);

        // 5. Seed Categories & Posts
        $this->call(PostSeeder::class);
    }
}

