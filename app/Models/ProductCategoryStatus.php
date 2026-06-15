<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductCategoryStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property string|null $translated_name
 * @property Carbon|null $deleted_at
 */
#[Table('product_category_statuses', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(ProductCategoryStatusFactory::class)]
class ProductCategoryStatus extends Model
{
    /** @use HasFactory<ProductCategoryStatusFactory> */
    use HasFactory;

    use SoftDeletes;

    public const int ACTIVE_ID = 1;

    public const int INACTIVE_ID = 2;

    /**
     * Get the translated name of the status.
     */
    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    public function getColor(): string
    {
        return match ($this->id) {
            self::ACTIVE_ID => 'success',
            self::INACTIVE_ID => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get the categories for the status.
     *
     * @return HasMany<ProductCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'product_category_status_id', 'id');
    }
}
