<?php

namespace App\Models;

use Database\Factories\PurchaseOrderStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PurchaseOrder> $purchaseOrders
 * @property-read int|null $purchase_orders_count
 *
 * @method static \Database\Factories\PurchaseOrderStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class PurchaseOrderStatus extends Model
{
    /** @use HasFactory<PurchaseOrderStatusFactory> */
    use HasFactory;

    /**
     * Get the purchase orders for the status.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
