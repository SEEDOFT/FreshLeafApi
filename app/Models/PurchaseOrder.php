<?php

namespace App\Models;

use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $supplier_id
 * @property int $purchase_order_status_id
 * @property string $po_number
 * @property Carbon $ordered_at
 * @property Carbon|null $received_at
 * @property numeric $total_cost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read PurchaseOrderStatus $status
 * @property-read Supplier $supplier
 */
#[Table('purchase_orders', key: 'id')]
#[Fillable([
    'supplier_id',
    'purchase_order_status_id',
    'po_number',
    'ordered_at',
    'received_at',
    'total_cost',
])]
#[UseFactory(PurchaseOrderFactory::class)]
class PurchaseOrder extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    /**
     * Get the supplier that owns the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the purchase order status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderStatus::class, 'purchase_order_status_id');
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
