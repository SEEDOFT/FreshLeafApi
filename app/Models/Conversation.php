<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property-read int $id
 * @property-read int $conversation_type_id
 * @property-read int $conversation_status_id
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read ConversationType $type
 * @property-read ConversationStatus $status
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, ConversationParticipant> $participants
 * @property-read int|null $participants_count
 */
#[Table('conversations', key: 'id', keyType: 'int')]
#[Fillable(['conversation_type_id', 'conversation_status_id'])]
class Conversation extends Model
{
    use SoftDeletes;

    /**
     * Get the belong type
     *
     * @return BelongsTo<ConversationType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ConversationType::class, 'conversation_type_id', 'id');
    }

    /**
     * Get the belong status
     *
     * @return BelongsTo<ConversationStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ConversationStatus::class, 'conversation_status_id', 'id');
    }

    /**
     * Get the messages
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id')->orderBy('created_at');
    }

    /**
     * Get the participants
     *
     * @return HasMany<ConversationParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id', 'id');
    }
}
