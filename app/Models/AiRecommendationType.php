<?php

namespace App\Models;

use Database\Factories\AiRecommendationTypeFactory;
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
 * @property-read Collection<int, AiRecommendation> $recommendations
 * @property-read int|null $recommendations_count
 *
 * @method static \Database\Factories\AiRecommendationTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class AiRecommendationType extends Model
{
    /** @use HasFactory<AiRecommendationTypeFactory> */
    use HasFactory;

    /**
     * Get the recommendations for the type.
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }
}
