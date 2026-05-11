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
 * @property int $ai_chat_session_id
 * @property int|null $user_id
 * @property string|null $message_id
 * @property string $role
 * @property string $content
 * @property string|null $status
 * @property int|null $sequence
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiChatSession $session
 * @property-read User|null $user
 */
#[Table('ai_chat_messages', key: 'id', keyType: 'int')]
#[Fillable([
    'ai_chat_session_id',
    'user_id',
    'message_id',
    'role',
    'content',
    'status',
    'sequence',
    'error',
])]
class AiChatMessage extends Model
{
    /**
     * Get the session that owns the AI chat message.
     *
     * @return BelongsTo<AiChatSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'ai_chat_session_id', 'id');
    }

    /**
     * Get the user that owns the AI chat message.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
