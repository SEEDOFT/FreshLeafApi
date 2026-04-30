<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string|null $name_km
 * @property string|null $description_en
 * @property string|null $description_km
 * @property string|null $image_url
 * @property bool $is_active
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 */
#[Table('product_categories')]
#[Fillable([
    'name_en',
    'name_km',
    'description_en',
    'description_km',
    'image_url',
    'is_active',
    'slug',
])]
class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory;

    /**
     * Get the localized name of the category.
     */
    public string $localizedName {
        get => App::getLocale() === 'km' ? ($this->name_km ?? $this->name_en) : $this->name_en;
    }

    /**
     * Get the localized description of the category.
     */
    public ?string $localizedDescription {
        get => App::getLocale() === 'km' ? ($this->description_km ?? $this->description_en) : $this->description_en;
    }

    /**
     * Get the products for the category.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
