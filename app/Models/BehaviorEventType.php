<?php

namespace App\Models;

use Database\Factories\BehaviorEventTypeFactory;
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
 * @property-read Collection<int, UserBehaviorEvent> $events
 * @property-read int|null $events_count
 * @method static \Database\Factories\BehaviorEventTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorEventType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class BehaviorEventType extends Model
{
    /** @use HasFactory<BehaviorEventTypeFactory> */
    use HasFactory;

    /**
     * Get the events for the type.
     */
    public function events(): HasMany
    {
        return $this->hasMany(UserBehaviorEvent::class);
    }
}
