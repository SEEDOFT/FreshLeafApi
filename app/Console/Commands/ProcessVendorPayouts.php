<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\VendorPayoutService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('payouts:process')]
#[Description('Process vendor payouts for delivered orders')]
class ProcessVendorPayouts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(VendorPayoutService $payoutService): int
    {
        $this->info('Starting vendor payout processing...');

        // 1 day ago
        $cutoffDate = Carbon::now()->subDay();

        $eligibleOrders = Order::where('order_status_id', OrderStatus::DELIVERED_ID)
            ->where('is_vendor_paid', false)
            ->where(function ($query) use ($cutoffDate) {
                $query->whereNotNull('consumer_confirmed_date')
                    ->orWhere('order_delivered_date', '<=', $cutoffDate);
            })
            ->get();

        $count = $eligibleOrders->count();
        $this->info("Found {$count} eligible orders for payout.");

        $successCount = 0;
        foreach ($eligibleOrders as $order) {
            try {
                if ($payoutService->payoutOrder($order)) {
                    $successCount++;
                    $this->line("Processed payout for Order #{$order->order_number}");
                }
            } catch (\Throwable $e) {
                $this->error("Failed to process payout for Order #{$order->order_number}: ".$e->getMessage());
            }
        }

        $this->info("Successfully processed {$successCount} payouts.");

        return self::SUCCESS;
    }
}
