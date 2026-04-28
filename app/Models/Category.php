<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

#[Table('categories')]
#[Fillable(['name_en', 'name_km', 'description_en', 'description_km', 'slug', 'image_url', 'is_active'])]
#[UseFactory(CategoryFactory::class)]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Get the localized name of the category.
     */
    public string $localizedName {
        get => App::getLocale() === 'km' ? $this->name_km : $this->name_en;
    }

    /**
     * Get the localized description of the category.
     */
    public ?string $localizedDescription {
        get => App::getLocale() === 'km' ? $this->description_km : $this->description_en;
    }

    /**
     * Get the products for the category.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'organic_category_id', 'id');
    }

    /**
     * {@inheritDoc}
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
