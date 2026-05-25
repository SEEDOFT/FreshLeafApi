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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name_en
 * @property string $name_km
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Notification> $notifications
 * @property-read int|null $notifications_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('notification_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(NotificationTypeFactory::class)]
class NotificationType extends Model
{
    use SoftDeletes;

    /** @use HasFactory<NotificationTypeFactory> */
    use HasFactory;

    public const int ORDER_UPDATE_ID = 1;

    public const int PROMOTION_ID = 2;

    public const int SYSTEM_ID = 3;

    public const string ORDER_UPDATE = 'ORDER_UPDATE';

    public const string PROMOTION = 'PROMOTION';

    public const string SYSTEM = 'SYSTEM';

    /**
     * Get the notifications for the type.
     *
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_type_id', 'id');
    }
}
