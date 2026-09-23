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
     * Display the admin binary MLM dashboard home based on NEXUS COMMUNITY PRD 2026.
     */
    /**
     * Display the admin & member dashboard based on NEXUS COMMUNITY PRD 2026.
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

        $activeVouchers = Voucher::where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($v) {
                return [
                    'code' => $v->code,
                    'package_name' => $v->package_name ?: 'Standard (Rp 100.000)',
                    'label' => $v->code . ' (' . ($v->package_name ?: 'Standard') . ')',
                ];
            });

        $voucherAktif = $activeVouchers->count();

        $isAdmin = ($user->username === 'admin' || $user->email === 'admin@nexuscommunity.id' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));

        $allSponsors = $isAdmin ? User::select('id', 'name', 'username')->get()->map(function ($u) {
            return [
                'username' => $u->username ?: ('user_' . $u->id),
                'name' => $u->name,
                'label' => '@' . ($u->username ?: ('user_' . $u->id)) . ' (' . $u->name . ')',
            ];
        }) : [];

        $settings = Setting::all()->pluck('value', 'key');
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

        return Inertia::render('Admin/Dashboard', [
            'is_admin' => $isAdmin,
            'current_user_username' => $user->username ?: 'user_' . $user->id,
            'vouchers' => $activeVouchers,
            'banks' => $banks,
            'registration_fee' => 100000,
            'all_sponsors' => $allSponsors,
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
     * NOTE: Per user request, unique code is removed. Amount is clean without unique code.
     */
    public function payPremi(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;
        $invoiceNumber = 'PRM-' . time() . '-' . $user->id;

        $premiPayment = \App\Models\PremiPayment::create([
            'user_id' => $user->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        return redirect()->route('admin.premi-invoice')->with([
            'premi_payment_id' => $premiPayment->id,
            'amount' => $amount,
            'invoice_number' => $invoiceNumber
        ]);
    }

    public function premiInvoice()
    {
        $user = auth()->user();

        // Find active/latest pending payment or one from session
        $premiPaymentId = session('premi_payment_id');
        if ($premiPaymentId) {
            $premiPayment = \App\Models\PremiPayment::where('id', $premiPaymentId)->where('user_id', $user->id)->first();
        } else {
            $premiPayment = \App\Models\PremiPayment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        if (!$premiPayment) {
            return redirect()->route('admin.dashboard');
        }

        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);
        $banks = (is_array($companyBanks) && count($companyBanks) > 0)
            ? $companyBanks
            : [
                [
                    'bank_name' => 'Bank BRI',
                    'account_number' => '806401000095564',
                    'bank_account_number' => '806401000095564',
                    'account_name' => 'PT.Nexus Community Punya Kita',
                    'bank_account_name' => 'PT.Nexus Community Punya Kita',
                ]
            ];

        return Inertia::render('Admin/PremiInvoice', [
            'premi_payment' => [
                'id' => $premiPayment->id,
                'invoice_number' => $premiPayment->invoice_number,
                'amount' => (float) $premiPayment->amount,
                'proof_of_transfer' => $premiPayment->proof_of_transfer ? asset($premiPayment->proof_of_transfer) : null,
                'status' => $premiPayment->status,
                'created_at' => $premiPayment->created_at->format('d M Y, H:i'),
            ],
            'amount' => (float) $premiPayment->amount,
            'total_transfer' => (float) $premiPayment->amount,
            'invoice_number' => $premiPayment->invoice_number,
            'banks' => $banks,
            'whatsapp_admin' => \App\Models\Setting::get('company_profile')['whatsapp'] ?? '6281234567890'
        ]);
    }

    /**
     * Upload proof of transfer for Premi invoice.
     */
    public function uploadPremiProof(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'premi_payment_id' => 'required|exists:premi_payments,id',
            'proof_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $user = auth()->user();
        $premiPayment = \App\Models\PremiPayment::where('id', $request->premi_payment_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $filename = 'proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/proofs', $filename, 'public');

            $premiPayment->update([
                'proof_of_transfer' => 'storage/' . $path,
            ]);
        }

        return back()->with('success', 'Bukti transfer berhasil diunggah! Mohon tunggu konfirmasi dari Admin.');
    }
}
