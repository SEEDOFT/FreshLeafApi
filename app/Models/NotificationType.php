<?php

namespace App\Models;

use Database\Factories\NotificationTypeFactory;
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
 * @method static \Database\Factories\NotificationTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['code', 'name'])]
class NotificationType extends Model
{
    /** @use HasFactory<NotificationTypeFactory> */
    use HasFactory;

    /**
     * Get the notifications for the type.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
