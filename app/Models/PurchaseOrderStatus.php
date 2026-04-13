<?php

namespace App\Models;

use Database\Factories\PurchaseOrderStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PurchaseOrder> $purchaseOrders
 * @property-read int|null $purchase_orders_count
 */
#[Table('purchase_order_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(PurchaseOrderStatusFactory::class)]
class PurchaseOrderStatus extends Model
{
    use HasFactory;

    /**
     * Get the purchase orders for the status.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
