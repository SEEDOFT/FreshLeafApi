<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NotificationStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
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
 */
#[Table('notification_statuses', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(NotificationStatusFactory::class)]
class NotificationStatus extends Model
{
    /** @use HasFactory<NotificationStatusFactory> */
    use HasFactory;

    /**
     * Get the notifications for the status.
     */
    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_status_id', 'id');
    }
}
