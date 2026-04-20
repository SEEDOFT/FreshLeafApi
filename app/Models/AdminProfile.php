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

#[Table('admin_profiles', key: 'id')]
#[Fillable([
    'user_id',
    'job_title',
])]
#[UseFactory(AdminProfileFactory::class)]
class AdminProfile extends Model
{
    /** @use HasFactory<AdminProfileFactory> */
    use HasFactory;

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
