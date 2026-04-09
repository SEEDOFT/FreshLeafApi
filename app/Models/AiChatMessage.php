<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ai_chat_session_id
 * @property int|null $user_id
 * @property string $message_id
 * @property string $role
 * @property string $content
 * @property string $status
 * @property int $sequence
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiChatSession $session
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereAiChatSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatMessage whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'ai_chat_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
