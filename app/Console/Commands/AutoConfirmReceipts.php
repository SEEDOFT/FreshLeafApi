<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\VendorPayoutService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Signature('app:auto-confirm-receipts')]
#[Description('Auto confirm receipts for orders delivered more than 15 minutes ago')]
class AutoConfirmReceipts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(VendorPayoutService $payoutService)
    {
        /** @var Collection<int, Order> $orders */
        $orders = Order::where('order_status_id', OrderStatus::DELIVERED_ID)
            ->whereNull('consumer_confirmed_date')
            ->where('order_delivered_date', '<=', Carbon::now()->subMinutes(15))
            ->get();

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, $payoutService) {
                $order->update([
                    'consumer_confirmed_date' => Carbon::now(),
                ]);

                $payoutService->payoutOrder($order);
            });
        }

        $this->info("Auto-confirmed {$orders->count()} orders.");
    }
}
