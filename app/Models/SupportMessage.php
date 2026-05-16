<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $support_ticket_id
 * @property string $sender_type
 * @property int $sender_id
 * @property string $message
 * @property string|null $file_path
 * @property string|null $filePath
 * @property bool $is_read
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SupportTicket $ticket
 * @property-read User $sender
 */
#[Table('support_messages', key: 'id', keyType: 'int')]
#[Fillable([
    'support_ticket_id',
    'sender_type',
    'sender_id',
    'message',
    'file_path',
    'is_read',
])]
class SupportMessage extends Model
{
    use SoftDeletes;

    public const string ADMIN = 'admin';

    public const string USER = 'user';

    /**
     * Get the support ticket that this message belongs to.
     *
     * @return BelongsTo<SupportTicket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id', 'id');
    }

    /**
     * Get the sender of the message.
     *
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
