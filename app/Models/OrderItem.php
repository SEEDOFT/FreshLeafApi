<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\MoneyService;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $vendor_inventory_id
 * @property string $product_name_snapshot
 * @property string $unit_snapshot
 * @property string $unit_price_snapshot
 * @property string $quantity
 * @property string $subtotal
 * @property string $commission_amount
 * @property string $vendor_net_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read VendorInventory $vendorInventory
 * @property Carbon|null $deleted_at
 */
#[Table('order_items', key: 'id')]
#[Fillable([
    'order_id',
    'vendor_inventory_id',
    'product_name_snapshot',
    'unit_snapshot',
    'unit_price_snapshot',
    'quantity',
    'subtotal',
    'commission_amount',
    'vendor_net_amount',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(static function (OrderItem $item): void {
            if ($item->order_id && $item->order && $item->order->commissionFeeHistory) {
                $rate = MoneyService::money($item->order->commissionFeeHistory->rate);
            } else {
                $rate = MoneyService::money(CommissionFee::current()->rate);
            }
            $subtotal = MoneyService::money($item->subtotal ?? '0.00');
            $commissionRate = MoneyService::div($rate, '100', 8);
            $commissionAmount = MoneyService::mul($subtotal, $commissionRate);
            $vendorNetAmount = MoneyService::sub($subtotal, $commissionAmount);

            $item->setAttribute('commission_amount', $commissionAmount);
            $item->setAttribute('vendor_net_amount', $vendorNetAmount);
        });
    }

    /**
     * Get the current commission percentage from settings.
     */
    public public(set) string $activeCommissionRate {
        get {
            if ($this->order_id && $this->order && $this->order->commissionFeeHistory) {
                return MoneyService::money($this->order->commissionFeeHistory->rate);
            }

            return MoneyService::money(CommissionFee::current()->rate);
        }
    }

    /**
     * Get the order that owns the item.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the vendor inventory for the item.
     *
     * @return BelongsTo<VendorInventory, $this>
     */
    public function vendorInventory(): BelongsTo
    {
        return $this->belongsTo(VendorInventory::class, 'vendor_inventory_id', 'id');
    }
}
