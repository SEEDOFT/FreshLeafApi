<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\VendorInventory;
use App\Notifications\InventoryExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class CheckExpiringInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expiring-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for vendor inventory that is close to expiration and send alerts';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $thresholdDays = 3; // Alert if expiring in 3 days or less
        $now = Carbon::now()->startOfDay();

        /** @var Collection<int, VendorInventory> $inventories */
        $inventories = VendorInventory::with(['vendor', 'product'])
            ->active()
            ->whereNotNull('harvest_date')
            ->whereNotNull('shelf_life_days')
            ->whereNull('expiring_alert_sent_at')
            ->get();

        $alertCount = 0;

        foreach ($inventories as $inventory) {
            $harvestDate = $inventory->harvest_date;
            $shelfLifeDays = $inventory->shelf_life_days;
            if ($harvestDate === null || $shelfLifeDays === null) {
                continue;
            }
            $expirationDate = $harvestDate->copy()->addDays($shelfLifeDays)->startOfDay();

            $daysRemaining = (int) $now->diffInDays($expirationDate, false);

            // If it's expiring within the threshold (and hasn't already expired too long ago, say <= -30 to avoid spamming very old ones)
            // But we only alert if it hasn't been sent.
            if ($daysRemaining <= $thresholdDays && $daysRemaining >= 0) {
                if ($inventory->vendor) {
                    $inventory->vendor->notify(new InventoryExpiringNotification($inventory, $daysRemaining));

                    $inventory->update([
                        'expiring_alert_sent_at' => Carbon::now(),
                    ]);

                    $alertCount++;
                    $this->info("Sent alert to Vendor {$inventory->vendor->id} for Inventory {$inventory->id} (Expires in {$daysRemaining} days)");
                }
            }
        }

        $this->info("Checked expiring inventory. Sent {$alertCount} alerts.");
    }
}
