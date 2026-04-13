<?php

namespace App\Models;

use Database\Factories\AdminTypeFactory;
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
#[Table('admin_types', key: 'id', keyType: 'int')]
#[Fillable(['name'])]
#[UseFactory(AdminTypeFactory::class)]
class AdminType extends Model
{
    use HasFactory;

    public const int SUPER_ADMIN = 1;

    public const int OPERATION = 2;

    public const int SUPPORT = 3;

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'admin_type_id');
    }
}
