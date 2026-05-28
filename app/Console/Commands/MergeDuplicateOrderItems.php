<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OrderItem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:merge-duplicates')]
#[Description('Merge duplicate order items in the database')]
class MergeDuplicateOrderItems extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $duplicates = OrderItem::select('order_id', 'vendor_inventory_id')
            ->groupBy('order_id', 'vendor_inventory_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $items = OrderItem::where('order_id', $duplicate->order_id)
                ->where('vendor_inventory_id', $duplicate->vendor_inventory_id)
                ->orderBy('id', 'asc')
                ->get();

            $primary = $items->first();

            if ($primary === null) {
                continue;
            }

            $itemsToMerge = $items->skip(1);

            foreach ($itemsToMerge as $item) {
                $primary->quantity = bcadd(number_format((float) $primary->quantity, 2, '.', ''), number_format((float) $item->quantity, 2, '.', ''), 2);
                $primary->subtotal = bcadd(number_format((float) $primary->subtotal, 2, '.', ''), number_format((float) $item->subtotal, 2, '.', ''), 2);
                $primary->commission_amount = bcadd(number_format((float) $primary->commission_amount, 2, '.', ''), number_format((float) $item->commission_amount, 2, '.', ''), 2);
                $primary->vendor_net_amount = bcadd(number_format((float) $primary->vendor_net_amount, 2, '.', ''), number_format((float) $item->vendor_net_amount, 2, '.', ''), 2);
                $item->delete();
            }

            $primary->save();
        }

        $this->info('Duplicate order items merged successfully.');

        return self::SUCCESS;
    }
}
