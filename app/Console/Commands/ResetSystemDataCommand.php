<?php

namespace App\Console\Commands;

use App\Models\BonusLog;
use App\Models\PremiPayment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RepeatOrder;
use App\Models\TeamPointRedemption;
use App\Models\TprRequest;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherTransfer;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ResetSystemDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:system-data {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all member accounts and transactional data, keeping ONLY Admin and Master Catalog Data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting System Data Reset (Keeping ONLY Admin & Master Catalog Data)...');

        Schema::disableForeignKeyConstraints();

        // 1. Clear all transaction logs, orders & requests
        BonusLog::query()->delete();
        $this->info('✓ Bonus logs cleared.');

        WalletTransaction::query()->delete();
        $this->info('✓ Wallet transactions cleared.');

        Withdrawal::query()->delete();
        $this->info('✓ Withdrawals cleared.');

        if (Schema::hasTable('premi_payments')) {
            PremiPayment::query()->delete();
            $this->info('✓ Premi payments cleared.');
        }

        RepeatOrder::query()->delete();
        $this->info('✓ Repeat orders cleared.');

        PurchaseOrder::query()->delete();
        $this->info('✓ Purchase orders cleared.');

        Voucher::query()->delete();
        $this->info('✓ Vouchers cleared.');

        VoucherTransfer::query()->delete();
        $this->info('✓ Voucher transfers cleared.');

        TeamPointRedemption::query()->delete();
        $this->info('✓ Team point redemptions cleared.');

        TprRequest::query()->delete();
        $this->info('✓ TPR requests cleared.');

        // 2. Delete all non-admin member user accounts
        $deletedUsers = User::where('username', '!=', 'admin')
            ->where('email', '!=', 'admin@nexuscommunity.com')
            ->where('email', '!=', 'admin@nexuscommunity.id')
            ->where('id', '>', 1)
            ->delete();

        $this->info("✓ Deleted {$deletedUsers} member user accounts.");

        // 3. Reset Admin user account stats to initial clean 0 state
        $admin = User::where('username', 'admin')
            ->orWhere('email', 'admin@nexuscommunity.com')
            ->orWhere('email', 'admin@nexuscommunity.id')
            ->orWhere('id', 1)
            ->first();

        if ($admin) {
            $admin->update([
                'saldo' => 0,
                'total_bonus' => 0,
                'parent_id' => null,
                'position' => null,
                'left_count' => 0,
                'right_count' => 0,
                'left_points' => 0,
                'right_points' => 0,
                'team_points' => 0,
                'ro_points' => 0,
                'po_points' => 0,
                'bonus_uncashed' => 0,
                'is_premier' => false,
                'premier_activated_at' => null,
                'premier_expires_at' => null,
            ]);
            $this->info("✓ Admin user @{$admin->username} reset to initial clean 0 state.");
        }

        Schema::enableForeignKeyConstraints();

        $productCount = Product::count();
        $this->info("✓ Preserved {$productCount} official products in catalog.");
        $this->info('=== SYSTEM DATA RESET COMPLETE ===');

        return 0;
    }
}
