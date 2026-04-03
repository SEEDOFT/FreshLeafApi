<?php

namespace App\Models;

use Database\Factories\AiRecommendationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $ai_recommendation_type_id
 * @property int $ai_recommendation_status_id
 * @property string $title
 * @property array<array-key, mixed>|null $payload
 * @property numeric $score
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, AiRecommendationItem> $items
 * @property-read int|null $items_count
 * @property-read AiRecommendationStatus $status
 * @property-read AiRecommendationType $type
 * @property-read User|null $user
 *
 * @method static \Database\Factories\AiRecommendationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereAiRecommendationStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereAiRecommendationTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiRecommendation whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['user_id', 'ai_recommendation_type_id', 'ai_recommendation_status_id', 'title', 'payload', 'score'])]
class AiRecommendation extends Model
{
    /** @use HasFactory<AiRecommendationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
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
     * Get the recommendation type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(AiRecommendationType::class, 'ai_recommendation_type_id');
    }

    /**
     * Get the recommendation status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AiRecommendationStatus::class, 'ai_recommendation_status_id');
    }

    /**
     * Get the items for the recommendation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(AiRecommendationItem::class);
    }
}
