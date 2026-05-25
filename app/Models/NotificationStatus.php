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
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property Carbon|null $deleted_at
 */
#[Table('notification_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(NotificationStatusFactory::class)]
class NotificationStatus extends Model
{
    /** @use HasFactory<NotificationStatusFactory> */
    use HasFactory, SoftDeletes;

    public const int UNREAD_ID = 1;

    public const int READ_ID = 2;

    public const string UNREAD = 'UNREAD';

    public const string READ = 'READ';

    /**
     * Get the notifications for the status.
     *
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_status_id', 'id');
    }
}
