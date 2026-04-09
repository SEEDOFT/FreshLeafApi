<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string $preferred_language
 * @property array<array-key, mixed>|null $preferences
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $pin
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile wherePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile wherePreferredLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumerProfile whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'pin',
    'date_of_birth',
    'gender',
    'preferred_language',
    'preferences',
])]
class ConsumerProfile extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
