<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = [];

        // 1. Recent Bonus Logs
        $bonusLogs = \App\Models\BonusLog::where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get();

        foreach ($bonusLogs as $b) {
            $notifications[] = [
                'id' => 'bonus_' . $b->id,
                'type' => 'bonus',
                'title' => 'Bonus Masuk',
                'message' => ($b->description ?: 'Bonus ' . ucfirst($b->category)) . ' sebesar Rp ' . number_format($b->amount, 0, ',', '.'),
                'time' => $b->created_at ? $b->created_at->diffForHumans() : 'Baru saja',
                'timestamp' => $b->created_at ? $b->created_at->timestamp : time(),
            ];
        }

        // 2. Recent Registered Members (Jaringan)
        $newMembers = \App\Models\User::where('parent_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        foreach ($newMembers as $m) {
            $notifications[] = [
                'id' => 'member_' . $m->id,
                'type' => 'network',
                'title' => 'Mitra Baru Bergabung',
                'message' => $m->name . ' (@' . $m->username . ') telah bergabung di jaringan Anda.',
                'time' => $m->created_at ? $m->created_at->diffForHumans() : 'Baru saja',
                'timestamp' => $m->created_at ? $m->created_at->timestamp : time(),
            ];
        }

        // 3. Withdrawal Updates
        $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
            ->latest()
            ->limit(15)
            ->get();

        foreach ($withdrawals as $w) {
            $title = 'Penarikan Saldo';
            if ($w->status === 'approved') $title = 'Penarikan Disetujui';
            if ($w->status === 'rejected') $title = 'Penarikan Ditolak';
            
            $notifications[] = [
                'id' => 'wd_' . $w->id,
                'type' => 'finance',
                'title' => $title,
                'message' => 'Penarikan Rp ' . number_format($w->amount, 0, ',', '.') . ' (' . ucfirst($w->status) . ')',
                'time' => $w->updated_at ? $w->updated_at->diffForHumans() : 'Baru saja',
                'timestamp' => $w->updated_at ? $w->updated_at->timestamp : time(),
            ];
        }
        
        // 4. Transfers (Wallet Transactions)
        $transfers = \App\Models\WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['transfer_out', 'transfer_in'])
            ->latest()
            ->limit(15)
            ->get();
            
        foreach ($transfers as $t) {
            $title = $t->type === 'transfer_in' ? 'Dana Masuk' : 'Dana Keluar';
            $notifications[] = [
                'id' => 'tf_' . $t->id,
                'type' => 'finance',
                'title' => $title,
                'message' => ($t->description ?: 'Transfer saldo') . ' Rp ' . number_format($t->amount, 0, ',', '.'),
                'time' => $t->created_at ? $t->created_at->diffForHumans() : 'Baru saja',
                'timestamp' => $t->created_at ? $t->created_at->timestamp : time(),
            ];
        }

        // Sort by timestamp desc
        usort($notifications, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return Inertia::render('Admin/Notifications', [
            'allNotifications' => array_slice($notifications, 0, 50)
        ]);
    }
}
