<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $session_id
 * @property string|null $title
 * @property Carbon|null $last_message_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, AiChatMessage> $messages
 * @property-read int|null $messages_count
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereLastMessageAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiChatSession whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'session_id',
    'title',
    'last_message_at',
])]
class AiChatSession extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class)->orderBy('created_at');
    }
}
