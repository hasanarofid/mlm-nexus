<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\Product;
use App\Models\User;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    // 0. Reset PHP OPcache if active
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }

    // 1. Run Git Pull & Composer dump-autoload if shell execution is supported
    $gitLog = '';
    $composerLog = '';
    if (function_exists('shell_exec')) {
        $gitOutput = @shell_exec('git pull origin master 2>&1');
        if ($gitOutput) {
            $gitLog = "Git Pull Output:\n" . $gitOutput . "\n\n";
        }
        $output = @shell_exec('composer dump-autoload 2>&1');
        if ($output) {
            $composerLog = "Composer Output:\n" . $output . "\n\n";
        }
    }

    // 2. Check if ?fresh=1 parameter is explicitly passed
    $isFresh = isset($_GET['fresh']) && $_GET['fresh'] === '1';

    // Allowed artisan commands that can be triggered via ?cmd=xxx
    $allowedCommands = [
        'git-pull' => [
            'command' => 'config:clear',
            'label'   => 'Git Pull & Clear Cache',
        ],
        'fix-ro-matching' => [
            'command' => 'fix:ro-matching-bonus',
            'label'   => 'Fix Matching Bonus RO',
        ],
        'fix-incentive-bonus' => [
            'command' => 'fix:incentive-bonus',
            'label'   => 'Fix Incentive Promotion Bonus',
        ],
        'fix-personal-rewards' => [
            'command' => 'fix:personal-rewards',
            'label'   => 'Fix Personal RO & PO Rewards',
        ],
        'seed-products' => [
            'command' => 'db:seed',
            'label'   => 'Update Katalog Produk RO & PO',
            'class'   => 'ProductSeeder',
        ],
        'seed-posts' => [
            'command' => 'db:seed',
            'label'   => 'Update / Seed Berita & Panduan Artikel (PostSeeder)',
            'class'   => 'PostSeeder',
        ],
        'seed-settings' => [
            'command' => 'db:seed',
            'label'   => 'Update / Insert Default Settings (company_profile, banks, dsb)',
            'class'   => 'SettingSeeder',
        ],
        'seed-all' => [
            'command' => 'db:seed',
            'label'   => 'Run All Seeders (DatabaseSeeder)',
            'class'   => 'DatabaseSeeder',
        ],
        'reset-data' => [
            'command' => 'reset:system-data',
            'label'   => 'Reset Total Data Member & Transaksi (Kecuali Admin, Yayan, Arif & Produk)',
        ],
    ];

    $cmdKey = $_GET['cmd'] ?? null;

    if ($cmdKey === 'reset-edbert') {
        // Reset status Premier & topup saldo edberttjen
        $edbert = User::where('username', 'edberttjen')->orWhere('email', 'edbert.tjen@gmail.com')->first();
        if (!$edbert) {
            $budi = User::where('username', 'budi')->first();
            $edbert = User::create([
                'name' => 'Edbert Tjen',
                'username' => 'edberttjen',
                'email' => 'edbert.tjen@gmail.com',
                'phone' => '082120000228',
                'password' => bcrypt('password'),
                'parent_id' => $budi ? $budi->id : 1,
                'package_name' => 'Standard',
                'saldo' => 10000000.00,
                'total_bonus' => 10000000.00,
                'is_premier' => false,
            ]);
        } else {
            $edbert->update([
                'is_premier' => false,
                'premier_activated_at' => null,
                'premier_expires_at' => null,
                'saldo' => 10000000.00,
                'total_bonus' => 10000000.00,
            ]);
        }

        echo "<!DOCTYPE html><html><head><title>Reset Edbert Tjen - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: Status @edberttjen Berhasil Di-Reset!</h1>";
        echo "<p>User <strong>@edberttjen</strong> (Edbert Tjen - <code>edbert.tjen@gmail.com</code>) telah diset ke:</p>";
        echo "<ul>";
        echo "<li><strong>Status Premier:</strong> Non-Premier (Siap Di-Upgrade)</li>";
        echo "<li><strong>Saldo E-Wallet:</strong> Rp 10.000.000</li>";
        echo "<li><strong>Default Password:</strong> <code>password</code></li>";
        echo "</ul>";
        echo "<p style='margin-top:20px;'><a href='/login' style='padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Login Sebagai @edberttjen & Tes Upgrade Premier</a> &nbsp; <a href='/run_migrate.php' style='padding:10px 18px;background:#64748b;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Kembali ke Panel Migrate</a></p>";
        echo "</div></body></html>";
        exit;
    }

    if ($cmdKey === 'test-premier-email') {
        // Send Premier Upgrade activation email to edbert.tjen@gmail.com
        $targetEmail = $_GET['email'] ?? 'edbert.tjen@gmail.com';
        $userObj = User::where('email', $targetEmail)->orWhere('username', 'edberttjen')->first() ?: User::first();

        $logs = [];
        try {
            $userObj->notify(new \App\Notifications\UpgradePremierActivatedNotification($userObj));
            $logs[] = "✓ Email Upgrade Premier Berhasil -> Dikirim ke {$targetEmail}";
        } catch (\Throwable $e) {
            $logs[] = "✕ Email Upgrade Premier Error: " . $e->getMessage();
        }

        echo "<!DOCTYPE html><html><head><title>Test Email Premier - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ Test Email Upgrade Premier Completed</h1>";
        echo "<p>Hasil uji coba pengiriman Email Upgrade Premier ke <strong>" . htmlspecialchars($targetEmail) . "</strong>:</p>";
        echo "<pre>" . implode("\n", $logs) . "\n\nServer SMTP: smtp-relay.brevo.com:587\nPengirim: noreply@nexuscommunity.id</pre>";
        echo "<p style='margin-top:20px;'><a href='/run_migrate.php' style='padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Kembali ke Panel Migrate</a></p>";
        echo "</div></body></html>";
        exit;
    }

    if ($cmdKey === 'test-email') {
        // Send registration welcome email to edbert.tjen@gmail.com
        $targetEmail = $_GET['email'] ?? 'edbert.tjen@gmail.com';
        $userObj = User::where('email', $targetEmail)->orWhere('username', 'edberttjen')->first() ?: User::first();

        $logs = [];
        try {
            $userObj->notify(new \App\Notifications\WelcomeRegisterNotification($userObj, 'password'));
            $logs[] = "✓ Email Pendaftaran Member Berhasil -> Dikirim ke {$targetEmail}";
        } catch (\Throwable $e) {
            $logs[] = "✕ Email Pendaftaran Error: " . $e->getMessage();
        }

        echo "<!DOCTYPE html><html><head><title>Test Email Registration - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ Test Email Pendaftaran & Aktivasi Executed</h1>";
        echo "<p>Pengiriman email tes pendaftaran ke <strong>" . htmlspecialchars($targetEmail) . "</strong>:</p>";
        echo "<pre>" . implode("\n", $logs) . "\n\nHost SMTP: smtp-relay.brevo.com:587\nFrom: noreply@nexuscommunity.id</pre>";
        echo "<p style='margin-top:20px;'><a href='/run_migrate.php' style='padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Kembali ke Panel Migrate</a></p>";
        echo "</div></body></html>";
        exit;
    }

    if ($cmdKey && isset($allowedCommands[$cmdKey])) {
        // Run specific artisan command
        $cmdConfig = $allowedCommands[$cmdKey];
        $args = [];

        if (in_array($cmdConfig['command'], ['db:seed', 'migrate', 'migrate:fresh'])) {
            $args['--force'] = true;
        }

        if (!empty($cmdConfig['class'])) {
            $args['--class'] = $cmdConfig['class'];
        }

        // Pass username argument if provided
        if (!empty($_GET['username'])) {
            $args['member_username'] = trim($_GET['username']);
        }

        $kernel->call($cmdConfig['command'], $args);
        $cmdOutput = $kernel->output();
        $action = $cmdConfig['label'] . (!empty($args['member_username']) ? " (@{$args['member_username']})" : '');

        // Fetch current active products summary
        $roCount = Product::where('type', 'ro')->count();
        $poCount = Product::where('type', 'po')->count();
        $allProducts = Product::orderBy('type')->get();

        echo "<!DOCTYPE html><html><head><title>{$action} - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}table{width:100%;border-collapse:collapse;margin-top:1rem;}th,td{padding:8px 12px;border:1px solid #e2e8f0;text-align:left;}th{background:#f1f5f9;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action}</h1>";
        echo "<pre>" . htmlspecialchars($cmdOutput ?: "Command selesai tanpa error.") . "</pre>";
        echo "<h3>Katalog Produk Saat Ini ($roCount Produk RO, $poCount Produk PO):</h3>";
        echo "<table><thead><tr><th>Tipe</th><th>Nama Produk</th><th>Harga</th><th>Isi/Qty</th><th>Poin</th></tr></thead><tbody>";
        foreach ($allProducts as $p) {
            echo "<tr><td><strong style='color:" . ($p->type === 'ro' ? '#5c3a21' : '#1653a1') . ";'>" . strtoupper($p->type) . "</strong></td><td>" . htmlspecialchars($p->name) . "</td><td>Rp " . number_format($p->price, 0, ',', '.') . "</td><td>" . $p->quantity . "</td><td>" . $p->points . " Poin</td></tr>";
        }
        echo "</tbody></table>";
        echo "<p style='margin-top:20px;'><a href='/admin/kelola-produk' style='display:inline-block;padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Halaman Kelola Produk</a></p>";
        echo "</div></body></html>";

    } elseif ($isFresh) {
        $kernel->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);
        $action = "Migrate Fresh & Seed";

        // Clear & rebuild application caches
        @$kernel->call('config:clear');
        @$kernel->call('route:clear');
        @$kernel->call('view:clear');

        echo "<!DOCTYPE html><html><head><title>Migration & Composer Runner - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action} Finished!</h1>";
        echo "<pre>" . htmlspecialchars($composerLog . ($kernel->output() ?: "Migration completed successfully with no pending migrations.")) . "</pre>";
        echo "<p style='margin-top:20px;'><a href='/admin/laporan' style='display:inline-block;padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Halaman Laporan</a></p>";
        echo "</div></body></html>";

    } else {
        $kernel->call('migrate', [
            '--force' => true,
        ]);
        $migrateLog = $kernel->output();
        
        // Auto seed DatabaseSeeder to ensure admin & default users are created/updated
        $kernel->call('db:seed', [
            '--class' => 'DatabaseSeeder',
            '--force' => true,
        ]);
        $seedLog = $kernel->output();

        // Ensure user @edberttjen exists with sufficient saldo for Premier upgrade testing
        $budiUser = User::where('username', 'budi')->first();
        $edbert = User::where('username', 'edberttjen')->orWhere('email', 'edbert.tjen@gmail.com')->first();
        if (!$edbert) {
            User::create([
                'name' => 'Edbert Tjen',
                'username' => 'edberttjen',
                'email' => 'edbert.tjen@gmail.com',
                'phone' => '082120000228',
                'password' => bcrypt('password'),
                'parent_id' => $budiUser ? $budiUser->id : 1,
                'package_name' => 'Standard',
                'saldo' => 10000000.00,
                'total_bonus' => 10000000.00,
                'is_premier' => false,
            ]);
        } else {
            if ($edbert->saldo < 5000000) {
                $edbert->update([
                    'saldo' => 10000000.00,
                    'total_bonus' => 10000000.00,
                ]);
            }
        }

        // Ensure all legacy Basic / null membership statuses are converted to Standard
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('package_name')
            ->orWhereIn('package_name', ['Basic', 'basic', '', 'Starter', 'Seller'])
            ->update(['package_name' => 'Standard']);

        if (\Illuminate\Support\Facades\Schema::hasTable('vouchers')) {
            \Illuminate\Support\Facades\DB::table('vouchers')
                ->whereNull('package_name')
                ->orWhereIn('package_name', ['Basic', 'basic', ''])
                ->update(['package_name' => 'Standard']);
        }

        // Ensure minimum withdrawal is set to 250.000
        \App\Models\Setting::setValue('min_withdrawal', 250000);

        // Ensure demo members have valid WhatsApp numbers
        $demoPhones = [
            'budi' => '081234567801',
            'siti' => '081234567802',
            'dewi' => '081234567803',
            'eko' => '081234567804',
            'fajar' => '081234567805',
            'edberttjen' => '082120000228',
        ];
        foreach ($demoPhones as $uname => $ph) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('username', $uname)
                ->where(function($q) {
                    $q->whereNull('phone')->orWhere('phone', '')->orWhere('phone', '-');
                })
                ->update(['phone' => $ph]);
        }

        $action = "Migrate & Seed Catalog (Update Only)";

        // Clear & rebuild application caches and bring app online (turn off maintenance mode)
        @$kernel->call('up');
        $downFile = __DIR__ . '/../storage/framework/down';
        if (file_exists($downFile)) {
            @unlink($downFile);
        }
        @$kernel->call('config:clear');
        @$kernel->call('route:clear');
        @$kernel->call('view:clear');

        $allUsers = User::select('id', 'name', 'username', 'email', 'package_name', 'is_premier', 'saldo')->get();
        $edbertUser = User::where('username', 'edberttjen')->orWhere('email', 'edbert.tjen@gmail.com')->first();

        echo "<!DOCTYPE html><html><head><title>Migration & Product Seeder - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:850px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}table{width:100%;border-collapse:collapse;margin-top:1rem;}th,td{padding:8px 12px;border:1px solid #e2e8f0;text-align:left;}th{background:#f1f5f9;}.box-tester{background:#fef3c7;border:1px solid #f59e0b;padding:1.25rem;border-radius:10px;margin-top:1.5rem;}.btn{display:inline-block;padding:9px 15px;border-radius:8px;font-weight:bold;text-decoration:none;font-size:13px;}.btn-gold{background:#d97706;color:#fff;}.btn-green{background:#10b981;color:#fff;}.btn-blue{background:#2563eb;color:#fff;}.btn-purple{background:#7c3aed;color:#fff;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action} Finished!</h1>";
        echo "<pre>" . htmlspecialchars($gitLog . $composerLog . ($migrateLog ?: "Database migration up-to-date.\n") . ($seedLog ?: "DatabaseSeeder executed successfully.\n") . "Status membership disinkronisasi ke Standard & Premier.\nMail Notification & Brevo SMTP Server Configured.") . "</pre>";
        
        if ($edbertUser) {
            echo "<div class='box-tester'>";
            echo "<h3 style='margin:0 0 8px;color:#92400e;'>⭐ Panel Uji Coba Email & Upgrade Premier Client (@edberttjen)</h3>";
            echo "<p style='margin:4px 0;'><strong>Nama:</strong> " . htmlspecialchars($edbertUser->name) . " | <strong>Username:</strong> @" . htmlspecialchars($edbertUser->username) . " | <strong>Email:</strong> " . htmlspecialchars($edbertUser->email) . "</p>";
            echo "<p style='margin:4px 0;'><strong>Saldo E-Wallet:</strong> Rp " . number_format($edbertUser->saldo, 0, ',', '.') . " | <strong>Status Premier:</strong> " . ($edbertUser->isPremier() ? "<span style='color:#16a34a;font-weight:bold;'>PREMIER ACTIVE</span>" : "<span style='color:#d97706;font-weight:bold;'>BELUM PREMIER (Siap Di-Upgrade)</span>") . "</p>";
            echo "<div style='margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;'>";
            echo "<a href='/run_migrate.php?cmd=test-premier-email' class='btn btn-purple'>📧 Kirim Test Email Upgrade Premier ke edbert.tjen@gmail.com</a>";
            echo "<a href='/run_migrate.php?cmd=test-email' class='btn btn-blue'>📧 Kirim Test Email Pendaftaran ke edbert.tjen@gmail.com</a>";
            echo "<a href='/run_migrate.php?cmd=reset-edbert' class='btn btn-gold'>🔄 Reset Premier & Top Up Saldo Rp 10 JT</a>";
            echo "<a href='/login' class='btn btn-green'>🔑 Login Ke Member Area (edberttjen)</a>";
            echo "</div>";
            echo "</div>";
        }

        echo "<h3 style='margin-top:1.5rem;'>Daftar User Akun Login (" . count($allUsers) . " Users):</h3>";
        echo "<table><thead><tr><th>ID</th><th>Nama</th><th>Username</th><th>Email</th><th>Saldo</th><th>Status</th><th>Password</th></tr></thead><tbody>";
        foreach ($allUsers as $u) {
            $statusBadge = $u->isPremier() ? "<strong style='color:#16a34a;'>Premier</strong>" : "Standard";
            echo "<tr><td>" . $u->id . "</td><td>" . htmlspecialchars($u->name) . "</td><td><strong>@" . htmlspecialchars($u->username) . "</strong></td><td>" . htmlspecialchars($u->email) . "</td><td>Rp " . number_format($u->saldo, 0, ',', '.') . "</td><td>" . $statusBadge . "</td><td><code>password</code></td></tr>";
        }
        echo "</tbody></table>";

        echo "<div style='margin-top:20px;display:flex;gap:10px;'>";
        echo "<a href='/run_migrate.php?fresh=1' style='padding:10px 18px;background:#ef4444;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Reset Fresh & Seed Database</a>";
        echo "<a href='/login' style='padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Halaman Login</a>";
        echo "</div>";
        echo "</div></body></html>";
    }
} catch (\Throwable $e) {
    echo "<!DOCTYPE html><html><head><title>Migration Error - NEXUS COMMUNITY</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}pre{background:#1e293b;color:#f87171;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
    echo "<div class='card'>";
    echo "<h1 style='color:#ef4444;'>✕ ERROR: Migration Failed</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div></body></html>";
}
