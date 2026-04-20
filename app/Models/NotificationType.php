<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NotificationTypeFactory;
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
#[Table('notification_types', key: 'id')]
#[Fillable(['name'])]
#[UseFactory(NotificationTypeFactory::class)]
class NotificationType extends Model
{
    use HasFactory;

    /**
     * Get the notifications for the type.
     */
    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_type_id', 'id');
    }
}
