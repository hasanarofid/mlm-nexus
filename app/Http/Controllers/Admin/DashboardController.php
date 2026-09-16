<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\BonusLog;
use App\Models\Voucher;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin binary MLM dashboard home based on XSELLER PRD 2026.
     */
    /**
     * Display the admin & member dashboard based on TALENTA52 PRD 2026.
     */
    public function index()
    {
        $user = auth()->user();

        $saldoWD = (float) ($user->saldo ?? 0);
        $autoSaveSaldo = (float) ($user->auto_save_saldo ?? 0);
        $totalSaldo = $saldoWD + $autoSaveSaldo;

        $bonusGenerasi = (float) BonusLog::where('user_id', $user->id)
            ->whereIn('category', ['generasi', 'bonus_generasi', 'sponsor', 'tier'])
            ->sum('amount');

        $directDownlines = User::where('parent_id', $user->id)->count();

        // Calculate total downlines up to 10 generations
        $totalDownlines = 0;
        $currentIds = [$user->id];
        for ($g = 1; $g <= 10; $g++) {
            if (empty($currentIds)) break;
            $downlineIds = User::whereIn('parent_id', $currentIds)->pluck('id')->toArray();
            $totalDownlines += count($downlineIds);
            $currentIds = $downlineIds;
        }

        $voucherAktif = Voucher::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        return Inertia::render('Admin/Dashboard', [
            'referral_links' => [
                'default' => url('/register?sponsor=' . ($user ? ($user->username ?: $user->id) : 1)),
                'url' => url('/register?sponsor=' . ($user ? ($user->username ?: $user->id) : 1)),
            ],
            'wallet' => [
                'total_saldo' => $totalSaldo,
                'auto_save_saldo' => $autoSaveSaldo,
                'saldo_wd' => $saldoWD,
                'saldo' => $saldoWD,
                'total_bonus' => (float) ($user->total_bonus ?? 0),
                'bonus_generasi' => $bonusGenerasi,
                'voucher_aktif' => $voucherAktif,
                'direct_downlines' => $directDownlines,
                'total_downlines' => $totalDownlines,
            ],
            'premi_info' => [
                'min_amount' => 10000,
                'description' => 'Pembayaran Premi bulanan (min Rp 10.000) masuk 100% full ke Total Saldo mitra.',
            ],
        ]);
    }

    /**
     * Process Monthly Premi payment (min Rp 10.000 - 100% full to Saldo WD).
     */
    public function payPremi(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;

        // Generate unique 3 digit code
        $uniqueCode = rand(100, 999);
        $totalTransfer = $amount + $uniqueCode;

        // Redirect to invoice page with amount data
        return redirect()->route('admin.premi-invoice')->with([
            'amount' => $amount,
            'unique_code' => $uniqueCode,
            'total_transfer' => $totalTransfer,
            'invoice_number' => 'PRM-' . time() . '-' . auth()->id()
        ]);
    }
    public function premiInvoice()
    {
        $amount = session('amount');
        if (!$amount) {
            return redirect()->route('admin.dashboard');
        }

        $banks = \App\Models\Setting::get('company_profile')['banks'] ?? [
            [
                'bank_name' => 'Bank BCA',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'Admin TALENTA52'
            ]
        ];

        return Inertia::render('Admin/PremiInvoice', [
            'amount' => $amount,
            'unique_code' => session('unique_code'),
            'total_transfer' => session('total_transfer'),
            'invoice_number' => session('invoice_number'),
            'banks' => $banks,
            'whatsapp_admin' => \App\Models\Setting::get('company_profile')['whatsapp'] ?? '6281234567890'
        ]);
    }
}
