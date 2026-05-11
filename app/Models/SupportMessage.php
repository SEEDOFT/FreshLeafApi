<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $support_ticket_id
 * @property string $sender_type
 * @property int $sender_id
 * @property string $message
 * @property bool $is_read
 * @property string|null $file_path
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
    'is_read',
    'file_path',
])]
class SupportMessage extends Model
{
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
     * Note: Admins are stored in the same users table but with a specific user_type_id.
     *
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
