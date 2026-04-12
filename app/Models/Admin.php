<?php

namespace App\Models;

use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'type_id',
    'status_id',
    'department',
    'job_title',
    'office_phone',
    'super_admin',
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
        return $this->belongsTo(AdminType::class, 'type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AdminStatus::class, 'status_id');
    }

    public function preference(): MorphOne
    {
        return $this->morphOne(PanelPreference::class, 'account');
    }
}
