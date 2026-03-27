<?php

namespace App\Models;

use Database\Factories\UserBehaviorEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $behavior_event_type_id
 * @property int|null $product_id
 * @property int|null $product_variant_id
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read BehaviorEventType $type
 * @property-read User|null $user
 * @property-read ProductVariant|null $variant
 * @method static \Database\Factories\UserBehaviorEventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereBehaviorEventTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBehaviorEvent whereUserId($value)
 * @mixin \Eloquent
 */
#[Fillable(['user_id', 'behavior_event_type_id', 'product_id', 'product_variant_id', 'metadata'])]
class UserBehaviorEvent extends Model
{
    /** @use HasFactory<UserBehaviorEventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(BehaviorEventType::class, 'behavior_event_type_id');
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
