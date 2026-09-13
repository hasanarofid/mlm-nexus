<?php

namespace App\Console\Commands;

use App\Models\BonusLog;
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
    protected $description = 'Reset all transactional data, member users, bonus logs, and orders except admin, yayan, arif & product catalog.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting System Data Reset (Keeping Admin, Yayan, Arif & Product Catalog)...');

        Schema::disableForeignKeyConstraints();

        // 1. Clear all transaction logs & orders
        BonusLog::query()->delete();
        $this->info('✓ Bonus logs cleared.');

        WalletTransaction::query()->delete();
        $this->info('✓ Wallet transactions cleared.');

        Withdrawal::query()->delete();
        $this->info('✓ Withdrawals cleared.');

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

        // Usernames to keep
        $preservedUsernames = ['admin', 'yayan', 'arif'];

        // 2. Delete all non-preserved users
        $deletedUsers = User::whereNotIn('username', $preservedUsernames)
            ->where('email', '!=', 'admin@xseller.id')
            ->where('id', '>', 1)
            ->delete();

        $this->info("✓ Deleted {$deletedUsers} other member user accounts.");

        // 3. Reset preserved users (Admin, Yayan, Arif)
        $preservedUsers = User::where(function($q) use ($preservedUsernames) {
            $q->whereIn('username', $preservedUsernames)
              ->orWhere('email', 'admin@xseller.id')
              ->orWhere('id', 1);
        })->get();

        foreach ($preservedUsers as $u) {
            $u->update([
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
            ]);
            $this->info("✓ User @{$u->username} ({$u->name}) reset to initial 0 state.");
        }

        Schema::enableForeignKeyConstraints();

        $productCount = Product::count();
        $this->info("✓ Preserved {$productCount} official products in catalog.");
        $this->info('=== SYSTEM DATA RESET COMPLETE ===');

        return 0;
    }
}
