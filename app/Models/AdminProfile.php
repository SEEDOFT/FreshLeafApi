<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $department
 * @property string|null $job_title
 * @property string|null $office_phone
 * @property bool $super_admin
 * @property array<array-key, mixed>|null $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereOfficePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereSuperAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'department',
    'job_title',
    'office_phone',
    'super_admin',
    'permissions',
])]
class AdminProfile extends Model
{
    protected function casts(): array
    {
        return [
            'super_admin' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
