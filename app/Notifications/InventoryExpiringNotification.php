<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VendorInventory $inventory,
        public int $daysRemaining
    ) {}

    /**
     * @param  object|User  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @param  User  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        $productName = $this->inventory->product->name_en;
        $harvestDate = $this->inventory->harvest_date;
        $shelfLifeDays = $this->inventory->shelf_life_days;
        $date = $harvestDate !== null && $shelfLifeDays !== null
            ? $harvestDate->copy()->addDays($shelfLifeDays)->format('M d, Y')
            : 'N/A';

        return (new MailMessage)
            ->subject("Inventory Alert: {$productName} is Expiring Soon")
            ->greeting("Hello {$notifiable->fullName},")
            ->line("Your inventory of **{$productName}** is close to its expiration date.")
            ->line("It is set to expire on **{$date}** ({$this->daysRemaining} days remaining).")
            ->line('Please review your inventory to avoid spoilage.')
            ->action('View Inventory', url('/app/vendor/vendor-inventories'))
            ->line('Thank you for using FreshLeaf!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inventory_expiring',
            'inventory_id' => $this->inventory->id,
            'product_name' => $this->inventory->product->name_en,
            'days_remaining' => $this->daysRemaining,
            'message' => "Your inventory of {$this->inventory->product->name_en} expires in {$this->daysRemaining} days.",
        ];
    }
}
