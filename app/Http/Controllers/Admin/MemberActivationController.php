<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\User;
use App\Models\Voucher;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MemberActivationController extends Controller
{
    /**
     * Display member activation form.
     */
    public function index()
    {
        $currentUser = auth()->user() ?: User::first();

        // Get user's active vouchers
        $activeVouchersRaw = Voucher::where('user_id', $currentUser->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('voucher_type')->orWhere('voucher_type', 'activation');
            })
            ->get();

        $vouchers = $activeVouchersRaw->map(function ($v) {
            $pkgName = $v->package_name ?: 'Seller';
            $pkgName = str_replace(
                ['Starter (Rp 125.000)', 'Starter', 'Basic (Rp 550.000)', 'Basic', 'Medium (Rp 2.100.000)', 'Medium', 'Pro (Rp 4.300.000)', 'Pro', 'Ultimate (Rp 10.500.000)', 'Ultimate'],
                ['Seller (Rp 125.000)', 'Seller', 'Star Seller (Rp 550.000)', 'Star Seller', 'Affiliate (Rp 2.100.000)', 'Affiliate', 'Business (Rp 4.300.000)', 'Business', 'Partner (Rp 10.500.000)', 'Partner'],
                $pkgName
            );
            return [
                'code' => $v->code,
                'package_name' => $pkgName,
                'label' => $v->code . ' (Paket ' . $pkgName . ')',
            ];
        });

        // Calculate voucher stock count per package type
        $voucherStocks = [
            'seller' => 0,
            'star_seller' => 0,
            'affiliate' => 0,
            'business' => 0,
            'partner' => 0,
        ];

        foreach ($activeVouchersRaw as $v) {
            $pkg = strtolower($v->package_name ?? '');
            if (str_contains($pkg, 'partner') || str_contains($pkg, 'ultimate') || str_contains($pkg, '10.500') || str_contains($pkg, '10500')) {
                $voucherStocks['partner']++;
            } elseif (str_contains($pkg, 'business') || str_contains($pkg, 'pro') || str_contains($pkg, '4.300') || str_contains($pkg, '4300')) {
                $voucherStocks['business']++;
            } elseif (str_contains($pkg, 'affiliate') || str_contains($pkg, 'medium') || str_contains($pkg, '2.100') || str_contains($pkg, '2100')) {
                $voucherStocks['affiliate']++;
            } elseif (str_contains($pkg, 'star') || str_contains($pkg, 'basic') || str_contains($pkg, '550')) {
                $voucherStocks['star_seller']++;
            } else {
                $voucherStocks['seller']++;
            }
        }

        // List of all active users to choose as Sponsor Langsung
        $allUsers = User::select('id', 'name', 'username', 'email')->get()->map(function ($u) {
            return [
                'username' => $u->username ?: strtolower(explode(' ', $u->name)[0]),
                'name' => $u->name,
                'label' => '@' . ($u->username ?: strtolower(explode(' ', $u->name)[0])) . ' (' . $u->name . ')',
            ];
        });

        // Company Banks for Direct Transfer Payment
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);
        $banks = (is_array($companyBanks) && count($companyBanks) > 0)
            ? $companyBanks
            : [
                [
                    'bank_name' => 'Bank BRI',
                    'bank_account_number' => '806401000095564',
                    'account_number' => '806401000095564',
                    'bank_account_name' => 'PT.Talenta52 Punya Kita',
                    'account_name' => 'PT.Talenta52 Punya Kita',
                ]
            ];

        return Inertia::render('Admin/Activation/Index', [
            'vouchers' => $vouchers,
            'voucher_stocks' => $voucherStocks,
            'banks' => $banks,
            'registration_fee' => 100000,
            'users' => $allUsers,
            'default_sponsor' => $currentUser->username ?: 'admin',
        ]);
    }

    /**
     * Process member activation in Matahari System (Direct Sponsor).
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|alpha_dash|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:25',
            'nik' => 'nullable|string|max:25',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'sponsor_username' => 'required|string|exists:users,username',
            'voucher_code' => 'nullable|string',
        ]);

        $currentUser = auth()->user() ?: User::first();
        $isAdmin = ($currentUser->username === 'admin' || $currentUser->email === 'admin@talenta52.com' || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('admin')));

        // Verify voucher / PIN if provided
        $voucher = null;
        if (!empty($request->voucher_code)) {
            $voucherQuery = Voucher::where('code', $request->voucher_code)->where('status', 'active');
            if (!$isAdmin) {
                $voucherQuery->where('user_id', $currentUser->id);
            }
            $voucher = $voucherQuery->first();

            if (!$voucher) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'Voucher Activation (PIN) tidak valid, telah digunakan, atau bukan milik Anda.',
                ]);
            }
        }

        $sponsorUser = User::where('username', $request->sponsor_username)->first();
        if (!$sponsorUser) {
            throw ValidationException::withMessages([
                'sponsor_username' => 'Username Sponsor Langsung tidak ditemukan.',
            ]);
        }

        $packageName = $voucher ? ($voucher->package_name ?: 'Standard (Rp 100.000)') : 'Standard (Rp 100.000)';
        $plainPassword = $request->password ?: 'password';

        DB::transaction(function () use ($request, $voucher, $sponsorUser, $packageName, $plainPassword) {
            // Create new member in Matahari system (parent_id = sponsor_id)
            $newUser = User::create([
                'name' => $request->name,
                'username' => strtolower($request->username),
                'email' => $request->email,
                'phone' => $request->phone ?? null,
                'nik' => $request->nik ?? null,
                'bank_name' => $request->bank_name ?: 'Bank BRI',
                'bank_account_number' => $request->bank_account_number ?? null,
                'bank_account_name' => $request->bank_account_name ?: $request->name,
                'password' => bcrypt($plainPassword),
                'parent_id' => $sponsorUser->id,
                'package_name' => $packageName,
            ]);
            $newUser->assignRole('client');

            try {
                $newUser->notify(new \App\Notifications\WelcomeRegisterNotification($newUser, $plainPassword));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengirim email aktivasi member: ' . $e->getMessage());
            }

            // Mark voucher as used if provided
            if ($voucher) {
                $voucher->update([
                    'status' => 'used',
                    'used_by_id' => $newUser->id,
                    'used_at' => now(),
                ]);
            }

            // 1. Allocation for Yayasan (Rp 10.000) & Founder (Rp 10.000) from Rp 100.000 registration fee
            $adminUser = User::where('username', 'admin')->first() ?: User::first();
            if ($adminUser) {
                BonusLog::create([
                    'transaction_code' => 'Y' . sprintf('%03d', BonusLog::count() + 1),
                    'user_id' => $adminUser->id,
                    'category' => 'yayasan',
                    'source_user_id' => $newUser->id,
                    'description' => "Alokasi Dana Yayasan (Rp 10.000) dari pendaftaran @{$newUser->username}",
                    'amount' => 10000,
                ]);

                BonusLog::create([
                    'transaction_code' => 'F' . sprintf('%03d', BonusLog::count() + 1),
                    'user_id' => $adminUser->id,
                    'category' => 'founder',
                    'source_user_id' => $newUser->id,
                    'description' => "Alokasi Dana Founder (Rp 10.000) dari pendaftaran @{$newUser->username}",
                    'amount' => 10000,
                ]);
            }

            // 2. Multi-tier Generation Bonus for Generasi 1 s/d Generasi 10 Uplines
            // Total bonus per generation = Rp 7.000 (50% Auto Save / Rp 3.500 & 50% Saldo WD / Rp 3.500)
            $bonusPerGen = 7000;
            $autoSaveShare = 3500;
            $wdShare = 3500;

            $currentUpline = $newUser;
            for ($gen = 1; $gen <= 10; $gen++) {
                if (!$currentUpline->parent_id) {
                    break;
                }

                $upline = User::find($currentUpline->parent_id);
                if (!$upline) {
                    break;
                }

                // Credit 50% Auto Save and 50% Saldo WD
                $upline->increment('auto_save_saldo', $autoSaveShare);
                $upline->increment('saldo', $wdShare);
                $upline->increment('total_bonus', $bonusPerGen);

                BonusLog::create([
                    'transaction_code' => 'G' . sprintf('%03d', BonusLog::count() + 1),
                    'user_id' => $upline->id,
                    'category' => 'generasi',
                    'source_user_id' => $newUser->id,
                    'description' => "Bonus Generasi {$gen}: Pendaftaran @{$newUser->username} (Total Rp 7.000: Auto Save Rp 3.500, Saldo WD Rp 3.500)",
                    'amount' => $bonusPerGen,
                ]);

                WalletTransaction::create([
                    'user_id' => $upline->id,
                    'type' => 'in',
                    'category' => 'bonus_generasi',
                    'amount' => $bonusPerGen,
                    'description' => "Bonus Generasi {$gen} dari pendaftaran @{$newUser->username} (50% Auto Save Rp 3.500, 50% Saldo WD Rp 3.500)",
                ]);

                try {
                    $upline->notify(new \App\Notifications\BonusReceivedNotification('generasi', (float) $bonusPerGen, "Bonus Generasi {$gen} dari pendaftaran @{$newUser->username}"));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Gagal mengirim email bonus generasi ke @{$upline->username}: " . $e->getMessage());
                }

                $currentUpline = $upline;
            }
        });

        $successMsg = "Mitra baru @{$request->username} ({$request->name}) berhasil didaftarkan di bawah Sponsor @{$sponsorUser->username}! Bonus Generasi 1-10 berhasil didistribusikan.";

        if ($request->input('source') === 'dashboard') {
            return redirect()->route('admin.dashboard')->with('success', $successMsg);
        }

        return redirect()->route('admin.pohon-jaringan', ['focus_id' => $sponsorUser->id])
            ->with('success', $successMsg);
    }

    /**
     * Calculate Package Allocation placeholder.
     */
    private function calculatePackageAllocation(string $packageName): array
    {
        return [
            'gen_1' => 7000,
            'gen_2_10' => 7000,
            'team_points' => 0,
        ];
    }
}
