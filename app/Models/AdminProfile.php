<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AdminProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $locale
 * @property string $prefer_theme
 * @property string|null $department
 * @property string|null $job_title
 * @property string|null $office_phone
 * @property bool $super_admin
 * @property array<string, mixed>|null $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Table('admin_profiles', key: 'id', keyType: 'int')]
#[Fillable([
    'user_id',
    'locale',
    'prefer_theme',
    'department',
    'job_title',
    'office_phone',
    'super_admin',
    'permissions',
])]
#[UseFactory(AdminProfileFactory::class)]
class AdminProfile extends Model
{
    /** @use HasFactory<AdminProfileFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'super_admin' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Get the user that owns the admin profile.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
