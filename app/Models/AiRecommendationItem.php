<?php

namespace App\Models;

use Database\Factories\AiRecommendationItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ai_recommendation_id
 * @property int $product_id
 * @property int $product_variant_id
 * @property numeric $suggested_qty
 * @property string|null $reason
 * @property numeric $estimated_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @property-read AiRecommendation $recommendation
 * @property-read ProductVariant $variant
 *
 * @method static \Database\Factories\AiRecommendationItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereAiRecommendationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereEstimatedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereSuggestedQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['ai_recommendation_id', 'product_id', 'product_variant_id', 'suggested_qty', 'reason', 'estimated_price'])]
class AiRecommendationItem extends Model
{
    /** @use HasFactory<AiRecommendationItemFactory> */
    use HasFactory;

    /**
     * Get the recommendation.
     */
    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(AiRecommendation::class, 'ai_recommendation_id');
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
