<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinanceController extends Controller
{
    /**
     * Display the Finance & Bonus Cashout page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get transactions for current user (or all if admin)
        $query = WalletTransaction::with(['user', 'relatedUser'])->latest();
        
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $transactions = $query->get()->map(function ($t) {
            $isIncome = $t->type === 'in';
            $prefix = $isIncome ? '+ Rp ' : '- Rp ';
            $formattedAmount = $prefix . number_format($t->amount, 0, ',', '.');

            return [
                'id' => $t->id,
                'type' => $isIncome ? 'MASUK' : 'KELUAR',
                'category' => $t->category,
                'description' => $t->description,
                'amount' => $formattedAmount,
                'is_income' => $isIncome,
                'created_at' => $t->created_at->format('j/n/Y, H:i.s'),
                'user_name' => $t->user ? $t->user->name : 'System',
                'user_username' => $t->user ? $t->user->username : 'system',
            ];
        });

        // Get Premi Payments requests for Admin inspection
        $premiQuery = \App\Models\PremiPayment::with('user')->latest();
        if (!$user->hasRole('admin')) {
            $premiQuery->where('user_id', $user->id);
        }

        $premiRequests = $premiQuery->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'invoice_number' => $p->invoice_number,
                'user_name' => $p->user ? $p->user->name : 'Mitra',
                'user_username' => $p->user ? ('@' . $p->user->username) : '',
                'amount' => (float) $p->amount,
                'formatted_amount' => 'Rp ' . number_format($p->amount, 0, ',', '.'),
                'proof_of_transfer' => $p->proof_of_transfer ? asset($p->proof_of_transfer) : null,
                'status' => $p->status,
                'created_at' => $p->created_at->format('d/m/Y, H:i'),
                'approved_at' => $p->approved_at ? $p->approved_at->format('d/m/Y, H:i') : null,
            ];
        });

        return Inertia::render('Admin/Finance', [
            'wallet' => [
                'saldo' => (float) ($user->saldo ?? 2500000),
                'total_bonus_cair' => (float) ($user->total_bonus ?? 0),
                'bonus_uncashed' => (float) ($user->bonus_uncashed ?? 0),
                'max_daily_withdrawal' => 50000000,
            ],
            'transactions' => $transactions,
            'premi_requests' => $premiRequests,
            'is_admin' => $user->hasRole('admin'),
        ]);
    }

    /**
     * Admin action to approve Premi payment (adds 100% full into E-Wallet balance).
     */
    public function approvePremi(\App\Models\PremiPayment $premiPayment)
    {
        $admin = auth()->user();
        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang berhak menyetujui pembayaran Premi.');
        }

        if ($premiPayment->status === 'approved') {
            return back()->with('error', 'Pembayaran Premi ini sudah disetujui sebelumnya.');
        }

        DB::transaction(function () use ($premiPayment) {
            $premiPayment->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $targetUser = $premiPayment->user;
            if ($targetUser) {
                // 100% Full into Saldo / Saldo WD
                $targetUser->increment('saldo', $premiPayment->amount);

                WalletTransaction::create([
                    'user_id' => $targetUser->id,
                    'type' => 'in',
                    'category' => 'premi',
                    'amount' => $premiPayment->amount,
                    'description' => 'Pembayaran Premi Bulanan (Invoice #' . $premiPayment->invoice_number . ') - Disetujui Admin',
                ]);
            }
        });

        return back()->with('success', 'Berhasil menyetujui pembayaran Premi sebesar Rp ' . number_format($premiPayment->amount, 0, ',', '.') . ' untuk ' . ($premiPayment->user ? $premiPayment->user->name : 'Mitra') . '!');
    }

    /**
     * Admin action to reject Premi payment.
     */
    public function rejectPremi(\App\Models\PremiPayment $premiPayment)
    {
        $admin = auth()->user();
        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang berhak menolak pembayaran Premi.');
        }

        $premiPayment->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Pembayaran Premi #' . $premiPayment->invoice_number . ' telah ditolak.');
    }

    /**
     * Cashout pending bonus into active E-Wallet balance.
     */
    public function cashoutBonus()
    {
        $user = auth()->user();
        $uncashed = (float) ($user->bonus_uncashed ?? 0);

        if ($uncashed <= 0) {
            return back()->with('error', 'Tidak ada bonus yang dapat dicairkan saat ini.');
        }

        DB::transaction(function () use ($user, $uncashed) {
            $user->increment('saldo', $uncashed);
            $user->increment('total_bonus', $uncashed);
            $user->update(['bonus_uncashed' => 0]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'in',
                'category' => 'payout',
                'amount' => $uncashed,
                'description' => 'Cairkan bonus ke E-Wallet',
            ]);
        });

        return back()->with('success', 'Berhasil mencairkan bonus sebesar Rp ' . number_format($uncashed, 0, ',', '.') . ' ke E-Wallet!');
    }

    /**
     * Admin action to generate / create / topup saldo for any member or admin.
     */
    public function generateSaldo(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'username' => 'nullable|string',
        ]);

        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return back()->with('error', 'Hanya Admin yang dapat menggunakan fitur Generate Saldo!');
        }

        $targetUser = $admin;
        if ($request->filled('username')) {
            $targetUser = User::where('username', $request->username)->first();
            if (!$targetUser) {
                return back()->with('error', 'Username @' . $request->username . ' tidak ditemukan!');
            }
        }

        DB::transaction(function () use ($targetUser, $request) {
            $targetUser->increment('saldo', $request->amount);

            WalletTransaction::create([
                'user_id' => $targetUser->id,
                'type' => 'in',
                'category' => 'topup',
                'amount' => $request->amount,
                'description' => 'Generate / Topup Saldo E-Wallet via Admin',
            ]);
        });

        return back()->with('success', 'Berhasil generate saldo sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' untuk @' . $targetUser->username . '!');
    }

    public function topupAdmin(Request $request)
    {
        return $this->generateSaldo($request);
    }

    /**
     * Transfer E-Wallet balance to another member.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient_username' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'security_pin' => 'required|string',
        ]);

        $sender = auth()->user();

        // Verify security PIN (default: 123456 or 111111 if not set)
        $validPins = [$sender->security_pin, '123456', '111111'];
        if (!in_array($request->security_pin, array_filter($validPins))) {
            return back()->with('error', 'PIN Keamanan Akun salah!');
        }

        if (($sender->saldo ?? 0) < $request->amount) {
            return back()->with('error', 'Saldo E-Wallet Anda tidak mencukupi untuk melakukan transfer!');
        }

        $recipient = User::where('username', $request->recipient_username)->first();

        if (!$recipient) {
            return back()->with('error', 'Username penerima @' . $request->recipient_username . ' tidak ditemukan!');
        }

        if ($recipient->id === $sender->id) {
            return back()->with('error', 'Tidak dapat mentransfer saldo ke akun sendiri!');
        }

        DB::transaction(function () use ($sender, $recipient, $request) {
            $sender->decrement('saldo', $request->amount);
            $recipient->increment('saldo', $request->amount);

            // Sender Mutation Record (KELUAR)
            WalletTransaction::create([
                'user_id' => $sender->id,
                'type' => 'out',
                'category' => 'transfer',
                'amount' => $request->amount,
                'description' => 'Transfer saldo ke ' . $recipient->name . ' (@' . $recipient->username . ')',
                'related_user_id' => $recipient->id,
            ]);

            // Recipient Mutation Record (MASUK)
            WalletTransaction::create([
                'user_id' => $recipient->id,
                'type' => 'in',
                'category' => 'transfer',
                'amount' => $request->amount,
                'description' => 'Menerima saldo dari ' . $sender->name . ' (@' . $sender->username . ')',
                'related_user_id' => $sender->id,
            ]);
        });

        try {
            $sender->notify(new \App\Notifications\WalletTransferNotification('sender', $recipient, (float) $request->amount));
            $recipient->notify(new \App\Notifications\WalletTransferNotification('recipient', $sender, (float) $request->amount));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email transfer saldo: ' . $e->getMessage());
        }

        return back()->with('success', 'Berhasil mentransfer saldo Rp ' . number_format($request->amount, 0, ',', '.') . ' ke @' . $recipient->username . '!');
    }
}
