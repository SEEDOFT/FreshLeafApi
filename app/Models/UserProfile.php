<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $pin
 */
#[Table('user_profiles', key: 'id')]
#[Fillable([
    'user_id',
    'pin',
    'gender',
    'preferred_language',
    'preferences',
])]
#[UseFactory(UserProfileFactory::class)]
class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
            'date_of_birth' => 'date',
            'preferences' => 'array',
        ];
    }

    /**
     * Belong to User

     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get or create a user profile for the given user.
     */
    public static function firstOrCreateForUser(User $user): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id],
            ['preferred_language' => 'en'],
        );
    }

    /**
     * Create a default profile for a new user.
     */
    public static function createDefaultForUser(User $user): self
    {
        return self::create([
            'user_id' => $user->id,
            'preferred_language' => 'en',
            'pin' => null,
        ]);
    }

    /**
     * Check if the user has a PIN set.
     */
    public function hasPin(): bool
    {
        return $this->pin !== null && $this->pin !== '';
    }

    /**
     * Verify the provided PIN against the stored hashed PIN.
     */
    public function verifyPin(string $pin): bool
    {
        return $this->hasPin() && Hash::check($pin, $this->pin);
    }

    /**
     * Set or update the user's PIN by hashing it before saving.
     */
    public function setPin(string $pin): void
    {
        $this->update(['pin' => $pin]);
    }
}
