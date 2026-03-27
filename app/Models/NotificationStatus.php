<?php

namespace App\Models;

use Database\Factories\NotificationStatusFactory;
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
 * @property-read Collection<int, Notification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Database\Factories\NotificationStatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationStatus whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class NotificationStatus extends Model
{
    /** @use HasFactory<NotificationStatusFactory> */
    use HasFactory;

    /**
     * Get the notifications for the status.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
