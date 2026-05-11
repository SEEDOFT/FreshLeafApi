<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, AiChatMessage> $messages
 * @property-read User $user
 */
#[Table('ai_chat_sessions', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'session_id',
    'title',
    'last_message_at',
])]
class AiChatSession extends Model
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the chat session.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the chat messages for the session.
     *
     * @return HasMany<AiChatMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'ai_chat_session_id', 'id')
            ->orderBy('created_at');
    }
}
