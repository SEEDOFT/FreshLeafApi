<?php

namespace App\Models;

use Database\Factories\AdminStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Admin[] $admins
 */
#[Table('admin_statuses', key: 'id', keyType: 'int')]
#[Fillable(['name'])]
#[UseFactory(AdminStatusFactory::class)]
class AdminStatus extends Model
{
    use HasFactory;

    public const int ACTIVE = 1;

    public const int INACTIVE = 2;

    public const int PENDING = 3;

    public const int SUSPENDED = 4;

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'admin_status_id');
    }
}
