<?php

namespace App\Models;

use Database\Factories\PurchaseOrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $product_id
 * @property int $product_variant_id
 * @property numeric $qty_ordered
 * @property numeric $qty_received
 * @property numeric $cost_per_unit
 * @property Carbon|null $expiry_date
 * @property string|null $batch_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read PurchaseOrder $purchaseOrder
 * @property-read ProductVariant $variant
 * @method static \Database\Factories\PurchaseOrderItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereBatchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCostPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereQtyOrdered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereQtyReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['purchase_order_id', 'product_id', 'product_variant_id', 'qty_ordered', 'qty_received', 'cost_per_unit', 'expiry_date', 'batch_code'])]
class PurchaseOrderItem extends Model
{
    /** @use HasFactory<PurchaseOrderItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    /**
     * Get the purchase order that owns the item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product for the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant for the item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
