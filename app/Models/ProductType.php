<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $translated_name
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('product_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
#[UseFactory(ProductTypeFactory::class)]
class ProductType extends Model
{
    use SoftDeletes;

    /** @use HasFactory<ProductTypeFactory> */
    use HasFactory;

    public const int DEFAULT_ID = 1;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'name_en' => 'string',
            'name_km' => 'string',
        ];
    }

    /**
     * Get the products for the product type.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_type_id', 'id');
    }

    public function getTranslatedNameProperty(): string
    {
        return $this->{'name_'.App::getLocale()};
    }
}
