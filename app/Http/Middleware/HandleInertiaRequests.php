<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notifications' => function () use ($request) {
                $user = $request->user();
                if (!$user) return [];

                $notifications = [];

                // 1. Recent Bonus Logs
                try {
                    $bonusLogs = \App\Models\BonusLog::where('user_id', $user->id)
                        ->latest()
                        ->limit(4)
                        ->get();

                    foreach ($bonusLogs as $b) {
                        $notifications[] = [
                            'id' => 'bonus_' . $b->id,
                            'type' => 'bonus',
                            'title' => 'Bonus Masuk',
                            'message' => ($b->description ?: 'Bonus ' . ucfirst($b->category)) . ' sebesar Rp ' . number_format($b->amount, 0, ',', '.'),
                            'time' => $b->created_at ? $b->created_at->diffForHumans() : 'Baru saja',
                            'timestamp' => $b->created_at ? $b->created_at->timestamp : time(),
                            'read' => false,
                            'link' => '/admin/laporan?tab=bonus',
                        ];
                    }
                } catch (\Throwable $e) {}

                // 2. Recent Direct Downlines (Mitra Baru)
                try {
                    $newDownlines = \App\Models\User::where('parent_id', $user->id)
                        ->latest()
                        ->limit(3)
                        ->get();

                    foreach ($newDownlines as $d) {
                        $notifications[] = [
                            'id' => 'downline_' . $d->id,
                            'type' => 'member',
                            'title' => 'Mitra Baru Bergabung',
                            'message' => $d->name . ' (@' . ($d->username ?: $d->id) . ') telah bergabung di jaringan Anda.',
                            'time' => $d->created_at ? $d->created_at->diffForHumans() : 'Baru saja',
                            'timestamp' => $d->created_at ? $d->created_at->timestamp : time(),
                            'read' => false,
                            'link' => '/admin/pohon-jaringan',
                        ];
                    }
                } catch (\Throwable $e) {}

                // 3. Recent Withdrawals
                try {
                    $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
                        ->latest()
                        ->limit(3)
                        ->get();

                    foreach ($withdrawals as $w) {
                        $notifications[] = [
                            'id' => 'wd_' . $w->id,
                            'type' => 'withdrawal',
                            'title' => 'Status Penarikan (WD)',
                            'message' => 'Penarikan Rp ' . number_format($w->amount, 0, ',', '.') . ' berstatus ' . strtoupper($w->status) . '.',
                            'time' => $w->created_at ? $w->created_at->diffForHumans() : 'Baru saja',
                            'timestamp' => $w->created_at ? $w->created_at->timestamp : time(),
                            'read' => false,
                            'link' => '/admin/penarikan-saldo',
                        ];
                    }
                } catch (\Throwable $e) {}

                // 4. Default System Welcome Notification if empty
                if (empty($notifications)) {
                    $notifications[] = [
                        'id' => 'system_welcome',
                        'type' => 'system',
                        'title' => 'Selamat Datang di Nexus Community',
                        'message' => 'Akun Anda aktif. Mulai bangun tim dan raih komisi multi-tier unilevel Anda!',
                        'time' => 'Sistem',
                        'timestamp' => time(),
                        'read' => false,
                        'link' => '/admin/dashboard',
                    ];
                }

                // Sort by timestamp desc
                usort($notifications, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

                return array_slice($notifications, 0, 8);
            },
        ];
    }
}
