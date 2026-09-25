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

        $currentUser = auth()->user() ?: User::first();
        $isAdmin = ($currentUser->username === 'admin' || $currentUser->email === 'admin@nexuscommunity.id' || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('admin')));

        // List of all active users to choose as Sponsor Langsung (Khusus Admin)
        $allUsers = $isAdmin ? User::select('id', 'name', 'username', 'email')->get()->map(function ($u) {
            return [
                'username' => $u->username ?: strtolower(explode(' ', $u->name)[0]),
                'name' => $u->name,
                'label' => '@' . ($u->username ?: strtolower(explode(' ', $u->name)[0])) . ' (' . $u->name . ')',
            ];
        }) : [];

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
                    'bank_account_name' => 'PT.Nexus Community Punya Kita',
                    'account_name' => 'PT.Nexus Community Punya Kita',
                ]
            ];

        return Inertia::render('Admin/Activation/Index', [
            'is_admin' => $isAdmin,
            'current_user_name' => $currentUser->name,
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
        $currentUser = auth()->user() ?: User::first();
        $isAdmin = ($currentUser->username === 'admin' || $currentUser->email === 'admin@nexuscommunity.id' || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('admin')));

        if (!$isAdmin) {
            $request->merge(['sponsor_username' => $currentUser->username]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone' => 'required|string|max:25',
            'is_left_handed' => 'required|string|in:Iya,Tidak',
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_birth_date' => 'required|date',
            'beneficiary_relation' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:25',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'ktp_image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'password' => 'required|string|min:8|confirmed',
            'sponsor_username' => 'required|string|exists:users,username',
        ]);

        $sponsorUser = User::where('username', $request->sponsor_username)->first();
        if (!$sponsorUser) {
            throw ValidationException::withMessages([
                'sponsor_username' => 'Username Sponsor Langsung tidak ditemukan.',
            ]);
        }

        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $file = $request->file('ktp_image');
            $filename = 'ktp_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('images/ktp');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $ktpPath = '/images/ktp/' . $filename;
        }

        // Generate username unik dari email
        $usernameBase = strtolower(explode('@', $request->email)[0]);
        $usernameBase = preg_replace('/[^a-z0-9_]/', '', $usernameBase) ?: 'member';
        $username = $usernameBase;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $usernameBase . $counter++;
        }

        $plainPassword = $request->password;

        DB::transaction(function () use ($request, $sponsorUser, $username, $ktpPath, $plainPassword) {
            // Create new member in Matahari system (parent_id = sponsor_id)
            $newUser = User::create([
                'name' => $request->name,
                'username' => $username,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_left_handed' => $request->is_left_handed,
                'beneficiary_name' => $request->beneficiary_name,
                'beneficiary_birth_date' => $request->beneficiary_birth_date,
                'beneficiary_relation' => $request->beneficiary_relation,
                'beneficiary_phone' => $request->emergency_phone,
                'emergency_phone' => $request->emergency_phone,
                'bank_name' => $request->bank_name ?: 'Bank BRI',
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name ?: $request->name,
                'ktp_image' => $ktpPath,
                'password' => bcrypt($plainPassword),
                'parent_id' => $sponsorUser->id,
            ]);
            $newUser->assignRole('client');

            try {
                $newUser->notify(new \App\Notifications\WelcomeRegisterNotification($newUser, $plainPassword));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengirim email aktivasi member: ' . $e->getMessage());
            }

            // Allocation Bonus Sponsor (Gen 1: Rp 250.000) & Bonus Tim (Gen 2-10: Rp 5.000 / member)
            $bonusPerGen = 5000;
            $sponsorBonus = 250000;

            // Direct Sponsor (Gen 1)
            $sponsorUser->increment('saldo', $sponsorBonus);
            $sponsorUser->increment('total_bonus', $sponsorBonus);

            BonusLog::create([
                'transaction_code' => 'SP' . sprintf('%03d', BonusLog::count() + 1),
                'user_id' => $sponsorUser->id,
                'category' => 'sponsor',
                'source_user_id' => $newUser->id,
                'description' => "Bonus Sponsor Langsung dari pendaftaran @{$newUser->username}",
                'amount' => $sponsorBonus,
            ]);

            WalletTransaction::create([
                'user_id' => $sponsorUser->id,
                'type' => 'in',
                'category' => 'bonus_sponsor',
                'amount' => $sponsorBonus,
                'description' => "Bonus Sponsor Langsung dari pendaftaran @{$newUser->username}",
            ]);

            // Multi-tier Gen 2 s/d Gen 10 Uplines (Rp 5.000 / level per member baru)
            $currentUpline = $sponsorUser;
            for ($gen = 2; $gen <= 10; $gen++) {
                if (!$currentUpline->parent_id) {
                    break;
                }

                $upline = User::find($currentUpline->parent_id);
                if (!$upline) {
                    break;
                }

                $upline->increment('saldo', $bonusPerGen);
                $upline->increment('total_bonus', $bonusPerGen);

                BonusLog::create([
                    'transaction_code' => 'G' . sprintf('%03d', BonusLog::count() + 1),
                    'user_id' => $upline->id,
                    'category' => 'generasi',
                    'source_user_id' => $newUser->id,
                    'description' => "Bonus Tim Gen {$gen} dari pendaftaran @{$newUser->username}",
                    'amount' => $bonusPerGen,
                ]);

                WalletTransaction::create([
                    'user_id' => $upline->id,
                    'type' => 'in',
                    'category' => 'bonus_generasi',
                    'amount' => $bonusPerGen,
                    'description' => "Bonus Tim Gen {$gen} dari pendaftaran @{$newUser->username}",
                ]);

                $currentUpline = $upline;
            }
        });

        $successMsg = "Mitra baru {$request->name} (@{$username}) berhasil didaftarkan di bawah Sponsor @{$sponsorUser->username}!";

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
