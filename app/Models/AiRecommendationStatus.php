<?php

namespace App\Models;

use Database\Factories\AiRecommendationStatusFactory;
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
 * @method static \Database\Factories\AiRecommendationStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendationStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class AiRecommendationStatus extends Model
{
    /** @use HasFactory<AiRecommendationStatusFactory> */
    use HasFactory;

    /**
     * Get the recommendations for the status.
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }
}
