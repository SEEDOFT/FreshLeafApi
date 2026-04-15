<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property int $admin_type_id
 * @property int $admin_status_id
 * @property string|null $department
 * @property string|null $job_title
 * @property string|null $office_phone
 * @property array<array-key, mixed>|null $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table('admins', key: 'id', keyType: 'int')]
#[Fillable([
    'first_name',
    'last_name',
    'email',
    'password',
    'admin_type_id',
    'admin_status_id',
    'department',
    'job_title',
    'office_phone',
    'permissions',
])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(AdminFactory::class)]
class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'super_admin' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AdminType::class, 'type_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AdminStatus::class, 'status_id', 'id');
    }
}
