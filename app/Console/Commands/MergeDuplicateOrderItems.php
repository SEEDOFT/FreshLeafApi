<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MergeDuplicateOrderItems extends Command
{
    protected $signature = 'orders:merge-duplicates';

    protected $description = 'Merge duplicate order items in the database';

    public function handle()
    {
        $duplicates = \App\Models\OrderItem::select('order_id', 'vendor_inventory_id')
            ->groupBy('order_id', 'vendor_inventory_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $items = \App\Models\OrderItem::where('order_id', $duplicate->order_id)
                ->where('vendor_inventory_id', $duplicate->vendor_inventory_id)
                ->orderBy('id', 'asc')
                ->get();

            $primary = $items->first();
            $itemsToMerge = $items->skip(1);

            foreach ($itemsToMerge as $item) {
                $primary->quantity += $item->quantity;
                $primary->subtotal += $item->subtotal;
                $primary->commission_amount += $item->commission_amount;
                $primary->vendor_net_amount += $item->vendor_net_amount;
                $item->delete();
            }

            $primary->save();
        }

        $this->info('Duplicate order items merged successfully.');
    }
}
