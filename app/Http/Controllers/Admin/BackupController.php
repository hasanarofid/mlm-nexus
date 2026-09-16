<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Models\Withdrawal;
use App\Models\VoucherTransfer;
use App\Models\WalletTransaction;
use App\Models\BonusLog;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    /**
     * Download complete database backup in JSON format.
     */
    public function downloadJson()
    {
        $backupData = [
            'app_info' => [
                'name' => 'talenta52.com',
                'version' => '2.4 Binary MLM',
                'exported_at' => now()->toIso8601String(),
                'exporter' => auth()->user() ? auth()->user()->username : 'admin',
            ],
            'database' => [
                'users' => User::all()->makeHidden(['password', 'remember_token']),
                'settings' => Setting::all(),
                'withdrawals' => Withdrawal::all(),
                'voucher_transfers' => VoucherTransfer::all(),
                'wallet_transactions' => WalletTransaction::all(),
                'bonus_logs' => BonusLog::all(),
            ],
        ];

        $jsonContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = 'talenta52_database_backup_' . date('Y-m-d_H-i-s') . '.json';

        return response($jsonContent, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
