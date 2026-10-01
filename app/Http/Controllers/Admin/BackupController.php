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
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * Ensure only admin users can access backup functionality.
     */
    private function checkAdminPermission()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $isAdmin = $user->username === 'admin' 
            || $user->email === 'admin@nexuscommunity.id' 
            || (method_exists($user, 'hasRole') && $user->hasRole('admin'));

        if (!$isAdmin) {
            abort(403, 'Akses ditolak. Fitur Backup DB hanya dapat diakses oleh Admin.');
        }
    }

    /**
     * Download complete database backup in SQL format.
     */
    public function downloadSql()
    {
        $this->checkAdminPermission();

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();

        $sql = "-- ==========================================================\n";
        $sql .= "-- Database Backup: NEXUS COMMUNITY SYSTEM\n";
        $sql .= "-- Exported At: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database Name: " . $dbName . "\n";
        $sql .= "-- ==========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";

        foreach ($tables as $tableObj) {
            $tableArr = (array) $tableObj;
            $tableName = reset($tableArr);

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table structure for table `$tableName`\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `$tableName`;\n";

            $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
            $createTableArr = (array) $createTable[0];
            $createTableStmt = $createTableArr['Create Table'] ?? end($createTableArr);
            $sql .= $createTableStmt . ";\n\n";

            // Table Data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `$tableName` (" . $rows->count() . " rows)\n";
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $keys = array_map(function ($key) {
                        return "`" . $key . "`";
                    }, array_keys($rowArray));
                    
                    $values = array_map(function ($val) {
                        if (is_null($val)) {
                            return 'NULL';
                        }
                        return DB::getPdo()->quote($val);
                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `$tableName` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $fileName = 'nexus_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Download complete database backup in JSON format.
     */
    public function downloadJson()
    {
        $this->checkAdminPermission();

        $backupData = [
            'app_info' => [
                'name' => 'nexuscommunity.id',
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
        $fileName = 'nexus_database_backup_' . date('Y-m-d_H-i-s') . '.json';

        return response($jsonContent, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
