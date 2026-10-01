<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles and Permissions
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Seed Master Data (Products, Posts, Settings)
        $this->call(ProductSeeder::class);
        $this->call(PostSeeder::class);
        $this->call(SettingSeeder::class);

        // 3. Seed ONLY President Director (Admin) User
        $admin = User::where('username', 'admin')
            ->orWhere('email', 'admin@nexuscommunity.com')
            ->orWhere('email', 'admin@nexuscommunity.id')
            ->orWhere('id', 1)
            ->first() ?: new User();

        $admin->fill([
            'name' => 'President Director (Admin)',
            'username' => 'admin',
            'email' => 'admin@nexuscommunity.com',
            'password' => bcrypt('password'),
            'left_count' => 0,
            'right_count' => 0,
            'left_points' => 0,
            'right_points' => 0,
            'package_name' => 'Standard',
            'saldo' => 0,
            'total_bonus' => 0,
            'is_premier' => false,
        ]);
        $admin->save();
        $admin->assignRole('admin');
    }
}
