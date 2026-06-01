<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('conversations', key: 'id', keyType: 'int')]
#[Fillable(['conversation_type_id', 'conversation_status_id'])]
class Conversation extends Model
{
    use SoftDeletes;

    /** @return BelongsTo<ConversationType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ConversationType::class, 'conversation_type_id', 'id');
    }

    /** @return BelongsTo<ConversationStatus, $this> */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ConversationStatus::class, 'conversation_status_id', 'id');
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id')->orderBy('created_at');
    }

    /** @return HasMany<ConversationParticipant, $this> */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id', 'id');
    }
}
