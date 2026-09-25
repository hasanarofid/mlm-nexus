<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BonusLog;
use App\Models\Withdrawal;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display the Reports dashboard for both Admin and Members.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user() ?: User::first();
        $isAdmin = ($currentUser->username === 'admin' || $currentUser->email === 'admin@nexuscommunity.id' || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('admin')));

        $type = $request->input('type', 'team');
        if (!in_array($type, ['team', 'bonus', 'withdrawal', 'mutasi'])) {
            $type = 'team';
        }

        $data = $this->getReportData($type, $currentUser, $isAdmin);
        $summary = $this->getSummaryMetrics($currentUser, $isAdmin);

        return Inertia::render('Admin/Reports', [
            'is_admin' => $isAdmin,
            'active_type' => $type,
            'report_data' => $data,
            'summary' => $summary,
            'current_user' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'username' => $currentUser->username ?: 'user_' . $currentUser->id,
            ],
        ]);
    }

    /**
     * Calculate summary metrics for header cards.
     */
    private function getSummaryMetrics($currentUser, bool $isAdmin): array
    {
        if ($isAdmin) {
            $totalMembers = User::count();
            $totalBonus = (float) BonusLog::sum('amount');
            $totalWd = (float) Withdrawal::where('status', 'approved')->sum('amount');
            $totalSaldo = (float) User::sum('saldo');

            return [
                'total_team' => $totalMembers,
                'total_bonus' => $totalBonus,
                'total_withdrawal' => $totalWd,
                'saldo_wallet' => $totalSaldo,
            ];
        }

        // Member Metrics
        $teamList = $this->getTeamMitraData($currentUser, false);
        $totalBonus = (float) BonusLog::where('user_id', $currentUser->id)->sum('amount');
        $totalWd = (float) Withdrawal::where('user_id', $currentUser->id)->where('status', 'approved')->sum('amount');
        $saldoWallet = (float) ($currentUser->saldo ?? 0);

        return [
            'total_team' => count($teamList),
            'total_bonus' => $totalBonus,
            'total_withdrawal' => $totalWd,
            'saldo_wallet' => $saldoWallet,
        ];
    }

    /**
     * Get array data based on report type and user role.
     */
    private function getReportData($type, $currentUser, bool $isAdmin)
    {
        if ($type === 'team') {
            return $this->getTeamMitraData($currentUser, $isAdmin);
        }

        if ($type === 'bonus') {
            $query = BonusLog::with(['user', 'sourceUser'])->latest();
            if (!$isAdmin) {
                $query->where('user_id', $currentUser->id);
            }

            return $query->get()->map(function ($b) {
                $catLabel = match ($b->category) {
                    'sponsor' => 'Bonus Sponsor Langsung',
                    'generasi' => 'Bonus Tim Generasi',
                    'tier', 'upgrade_tier' => 'Bonus Upgrade Premium',
                    default => ucfirst(str_replace('_', ' ', $b->category)),
                };

                return [
                    'id' => $b->id,
                    'code' => $b->transaction_code ?: ('B' . str_pad($b->id, 3, '0', STR_PAD_LEFT)),
                    'name' => $b->user ? $b->user->name : 'Member',
                    'username' => $b->user ? ($b->user->username ?: 'user_' . $b->user->id) : '-',
                    'email' => $b->user ? $b->user->email : '-',
                    'category' => $catLabel,
                    'raw_category' => $b->category,
                    'source' => $b->sourceUser ? ('@' . ($b->sourceUser->username ?: $b->sourceUser->id)) : '-',
                    'source_name' => $b->sourceUser ? $b->sourceUser->name : '-',
                    'description' => $b->description,
                    'amount' => (float) $b->amount,
                    'created_at' => $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        if ($type === 'withdrawal') {
            $query = Withdrawal::with('user')->latest();
            if (!$isAdmin) {
                $query->where('user_id', $currentUser->id);
            }

            return $query->get()->map(function ($w) {
                return [
                    'id' => $w->id,
                    'code' => 'WD' . str_pad($w->id, 4, '0', STR_PAD_LEFT),
                    'name' => $w->user ? $w->user->name : 'Member',
                    'username' => $w->user ? ($w->user->username ?: 'user_' . $w->user->id) : '-',
                    'email' => $w->user ? $w->user->email : '-',
                    'bank_name' => $w->bank_name,
                    'bank_account_number' => $w->bank_account_number,
                    'bank_account_name' => $w->bank_account_name,
                    'amount' => (float) $w->amount,
                    'fee' => (float) ($w->fee ?? 0),
                    'status' => strtoupper($w->status),
                    'admin_notes' => $w->admin_notes ?: '-',
                    'created_at' => $w->created_at ? $w->created_at->format('d/m/Y H:i') : '-',
                    'processed_at' => $w->processed_at ? $w->processed_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        if ($type === 'mutasi') {
            $query = WalletTransaction::with('user')->latest();
            if (!$isAdmin) {
                $query->where('user_id', $currentUser->id);
            }

            return $query->get()->map(function ($t) {
                return [
                    'id' => $t->id,
                    'code' => 'TX' . str_pad($t->id, 4, '0', STR_PAD_LEFT),
                    'name' => $t->user ? $t->user->name : 'Member',
                    'username' => $t->user ? ($t->user->username ?: 'user_' . $t->user->id) : '-',
                    'email' => $t->user ? $t->user->email : '-',
                    'category' => ucfirst(str_replace('_', ' ', $t->category)),
                    'description' => $t->description,
                    'type' => $t->type === 'in' ? 'MASUK' : 'KELUAR',
                    'amount' => (float) $t->amount,
                    'created_at' => $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-',
                ];
            });
        }

        return [];
    }

    /**
     * Get team / downline members up to 10 generations.
     */
    private function getTeamMitraData($currentUser, bool $isAdmin): array
    {
        if ($isAdmin) {
            return User::with('parent')
                ->latest()
                ->get()
                ->map(function ($u) {
                    $sponsorLabel = $u->parent 
                        ? ('@' . ($u->parent->username ?: 'user_' . $u->parent->id) . ' (' . $u->parent->name . ')') 
                        : 'DIRECT / PERUSAHAAN';

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'username' => $u->username ?: ('user_' . $u->id),
                        'email' => $u->email ?: '-',
                        'phone' => $u->phone ?: '-',
                        'generation' => 'Direct / Admin',
                        'gen_level' => 0,
                        'sponsor' => $sponsorLabel,
                        'tier' => $u->package_name ?: 'Standard',
                        'saldo' => (float) ($u->saldo ?? 0),
                        'created_at' => $u->created_at ? $u->created_at->format('d/m/Y H:i') : '-',
                    ];
                })
                ->toArray();
        }

        // Downlines Gen 1 to Gen 10 for Member
        $team = [];
        $currentParentIds = [$currentUser->id];

        for ($gen = 1; $gen <= 10; $gen++) {
            if (empty($currentParentIds)) {
                break;
            }

            $downlines = User::whereIn('parent_id', $currentParentIds)
                ->with('parent')
                ->latest()
                ->get();

            if ($downlines->isEmpty()) {
                break;
            }

            foreach ($downlines as $u) {
                $sponsorLabel = $u->parent 
                    ? ('@' . ($u->parent->username ?: 'user_' . $u->parent->id) . ' (' . $u->parent->name . ')') 
                    : ('@' . ($currentUser->username ?: 'user_' . $currentUser->id));

                $team[] = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username ?: ('user_' . $u->id),
                    'email' => $u->email ?: '-',
                    'phone' => $u->phone ?: '-',
                    'generation' => $gen === 1 ? 'Generasi 1 (Sponsor Langsung)' : "Generasi {$gen}",
                    'gen_level' => $gen,
                    'sponsor' => $sponsorLabel,
                    'tier' => $u->package_name ?: 'Standard',
                    'saldo' => (float) ($u->saldo ?? 0),
                    'created_at' => $u->created_at ? $u->created_at->format('d/m/Y H:i') : '-',
                ];
            }

            $currentParentIds = $downlines->pluck('id')->toArray();
        }

        return $team;
    }

    /**
     * Export data to native Excel spreadsheet format (.xlsx / HTML table).
     */
    public function exportExcel(Request $request)
    {
        $currentUser = auth()->user() ?: User::first();
        $isAdmin = ($currentUser->username === 'admin' || $currentUser->email === 'admin@nexuscommunity.id' || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('admin')));

        $type = $request->input('type', 'team');
        if (!in_array($type, ['team', 'bonus', 'withdrawal', 'mutasi'])) {
            $type = 'team';
        }

        $filename = "Laporan_" . ucfirst($type) . "_" . ($currentUser->username ?: 'user') . "_" . date('Y-m-d') . ".xls";
        $data = $this->getReportData($type, $currentUser, $isAdmin);

        $headers = [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<style>';
        $html .= 'th { background-color: #0F172A; color: #D4AF37; font-weight: bold; text-align: left; padding: 10px; border: 1px solid #000000; font-family: sans-serif; font-size: 11px; }';
        $html .= 'td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: middle; font-family: sans-serif; font-size: 11px; }';
        $html .= '.title { font-size: 14px; font-weight: bold; color: #0F172A; margin-bottom: 12px; font-family: sans-serif; }';
        $html .= '.number { text-align: right; font-weight: bold; }';
        $html .= '</style></head><body>';

        $titleLabel = match ($type) {
            'team' => 'LAPORAN TEAM MITRA',
            'bonus' => 'LAPORAN BONUS',
            'withdrawal' => 'LAPORAN PENARIKAN SALDO (WITHDRAWAL)',
            'mutasi' => 'LAPORAN MUTASI SALDO',
            default => 'LAPORAN SISTEM',
        };

        $html .= '<div class="title">' . $titleLabel . ' - NEXUS COMMUNITY (' . date('d/m/Y H:i') . ')</div>';
        $html .= '<p style="font-size:10px;color:#666;">User: ' . htmlspecialchars($currentUser->name) . ' (@' . htmlspecialchars($currentUser->username) . ')</p>';
        $html .= '<table>';

        if ($type === 'team') {
            $html .= '<thead><tr><th>NO</th><th>NAMA MITRA</th><th>USERNAME</th><th>WHATSAPP / HP</th><th>EMAIL</th><th>GENERASI</th><th>SPONSOR LANGSUNG</th><th>STATUS MEMBERSHIP</th><th>TGL DAFTAR</th></tr></thead><tbody>';
            $no = 1;
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>' . $no++ . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['phone']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['email']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['generation']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['sponsor']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['tier']) . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        } elseif ($type === 'bonus') {
            $html .= '<thead><tr><th>KODE</th><th>NAMA MEMBER</th><th>USERNAME</th><th>JENIS BONUS</th><th>SUMBER MITRA</th><th>DESKRIPSI</th><th>NOMINAL BONUS (RP)</th><th>TANGGAL & WAKTU</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['category']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['source']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['amount'], 0, ',', '.') . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        } elseif ($type === 'withdrawal') {
            $html .= '<thead><tr><th>ID PENARIKAN</th><th>NAMA MEMBER</th><th>USERNAME</th><th>BANK TUJUAN</th><th>NO REKENING</th><th>ATAS NAMA</th><th>NOMINAL WD (RP)</th><th>STATUS</th><th>TGL PENGAJUAN</th><th>CATATAN ADMIN</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['bank_name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['bank_account_number']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['bank_account_name']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['amount'], 0, ',', '.') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['status']) . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '<td>' . htmlspecialchars($row['admin_notes']) . '</td>';
                $html .= '</tr>';
            }
        } elseif ($type === 'mutasi') {
            $html .= '<thead><tr><th>KODE</th><th>NAMA MEMBER</th><th>USERNAME</th><th>KATEGORI</th><th>TIPE</th><th>DESKRIPSI</th><th>NOMINAL (RP)</th><th>TANGGAL</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td>@' . htmlspecialchars($row['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['category']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['type']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
                $html .= '<td class="number">Rp ' . number_format($row['amount'], 0, ',', '.') . '</td>';
                $html .= '<td>' . $row['created_at'] . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, $headers);
    }

    /**
     * Export data to PDF / Printable View.
     */
    public function exportPdf(Request $request)
    {
        $currentUser = auth()->user() ?: User::first();
        $isAdmin = ($currentUser->username === 'admin' || $currentUser->email === 'admin@nexuscommunity.id' || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('admin')));

        $type = $request->input('type', 'team');
        if (!in_array($type, ['team', 'bonus', 'withdrawal', 'mutasi'])) {
            $type = 'team';
        }

        $data = $this->getReportData($type, $currentUser, $isAdmin);
        $title = match ($type) {
            'team' => 'Laporan Team Mitra',
            'bonus' => 'Laporan Bonus',
            'withdrawal' => 'Laporan Penarikan Saldo (Withdrawal)',
            'mutasi' => 'Laporan Mutasi Saldo',
            default => 'Laporan',
        };
        $date = date('d F Y, H:i');

        return response()->view('reports.pdf', compact('title', 'type', 'data', 'date', 'currentUser', 'isAdmin'));
    }
}
