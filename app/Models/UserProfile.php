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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $pin
 * @property string|null $gender
 * @property string $locale
 * @property string $theme
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property Carbon|null $deleted_at
 */
#[Table('user_profiles', key: 'id', keyType: 'int')]
#[Fillable(['user_id', 'pin', 'gender', 'locale', 'theme'])]
#[UseFactory(UserProfileFactory::class)]
class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->where('users.user_type_id', UserType::CONSUMER_ID);
    }

    /**
     * Get or create a user profile for the given user.
     */
    public static function firstOrCreateForUser(User $user): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id],
            ['locale' => 'en'],
        );
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
        return $this->hasPin() && Hash::check($pin, (string) $this->pin);
    }

    /**
     * Set or update the user's PIN by hashing it before saving.
     */
    public function setPin(string $pin): void
    {
        $this->update(['pin' => Hash::make($pin)]);
    }
}
