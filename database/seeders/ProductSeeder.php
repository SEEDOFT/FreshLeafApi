<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIdsBySlug = ProductCategory::query()->pluck('id', 'slug');
        $unitIdsBySymbol = Unit::query()->pluck('id', 'symbol');
        $typeIdsByCode = ProductType::query()->pluck('id', 'code');

        $catalog = [
            'leafy-vegetables' => [
                ['name' => 'Morning Glory', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Popular stir-fry leafy vegetable in Cambodia'],
                ['name' => 'Bok Choy', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Crunchy leafy vegetable for soups and stir-fry'],
                ['name' => 'Chinese Kale', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Firm leaves and stems, common in local markets'],
                ['name' => 'Water Spinach Bunch', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Sold by bunch quantity for quick retail turnover'],
                ['name' => 'Lettuce Head', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Sold per head for salad and fresh serving'],
                ['name' => 'Mustard Green', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Suitable for soup and salted vegetable dishes'],
            ],
            'fruiting-vegetables' => [
                ['name' => 'Tomato', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Core ingredient for sauces and soups'],
                ['name' => 'Cucumber', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Fresh salad vegetable, high daily demand'],
                ['name' => 'Eggplant', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Used in soups and grilled dishes'],
                ['name' => 'Bitter Melon', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Traditional bitter flavor vegetable'],
                ['name' => 'Pumpkin', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Sold per fruit for household and restaurant use'],
                ['name' => 'Bottle Gourd', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Commonly sold per piece in fresh markets'],
            ],
            'root-vegetables' => [
                ['name' => 'Carrot', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Used in stir-fry, soup, and salad'],
                ['name' => 'White Radish', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Popular in soups and pickled dishes'],
                ['name' => 'Sweet Potato', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Root crop with stable demand'],
                ['name' => 'Cassava Root', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Used for boiling, steaming, and flour processing'],
                ['name' => 'Turmeric Root', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Cooking and medicinal aromatic root'],
                ['name' => 'Galangal Root', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Essential aromatic root in Khmer cuisine'],
            ],
            'herbs-aromatic-plants' => [
                ['name' => 'Lemongrass', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Aromatic herb sold as stalk bundles'],
                ['name' => 'Kaffir Lime Leaves', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Fragrant leaves used in soup and curry'],
                ['name' => 'Holy Basil', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Fresh aromatic herb sold in bunches'],
                ['name' => 'Mint', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Used in fresh rolls and salads'],
                ['name' => 'Coriander', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Garnish and seasoning herb'],
                ['name' => 'Spring Onion', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Daily cooking aromatic sold by bundle'],
            ],
            'legumes' => [
                ['name' => 'Long Bean', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Main legume in Cambodian stir-fry dishes'],
                ['name' => 'Yardlong Bean', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'High-volume fresh market legume'],
                ['name' => 'Green Bean', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Short-cycle vegetable with steady demand'],
                ['name' => 'Snow Pea', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Crisp legume for premium menu items'],
                ['name' => 'Soybean Sprout Pack', 'unit' => 'qty', 'type' => 'fresh', 'detail' => 'Ready-to-cook sprouts sold by pack quantity'],
                ['name' => 'Peanut Fresh Pod', 'unit' => 'kg', 'type' => 'fresh', 'detail' => 'Local snack and ingredient legume'],
            ],
        ];

        $defaultStatusId = ProductStatus::ACTIVE;
        $fallbackUnitId = $unitIdsBySymbol['kg'] ?? null;
        $fallbackTypeId = $typeIdsByCode['fresh'] ?? null;

        foreach ($catalog as $categorySlug => $items) {
            $categoryId = $categoryIdsBySlug[$categorySlug] ?? null;

            if (! $categoryId) {
                continue;
            }

            foreach ($items as $item) {
                $unitId = $unitIdsBySymbol[$item['unit']] ?? $fallbackUnitId;
                $typeId = $typeIdsByCode[$item['type']] ?? $fallbackTypeId;

                if (! $unitId || ! $typeId) {
                    continue;
                }

                $slug = Str::slug($item['name']);

                $attributes = [
                    'product_category_id' => $categoryId,
                    'product_type_id' => $typeId,
                    'default_unit_id' => $unitId,
                    'product_status_id' => $defaultStatusId,
                    'name' => $item['name'],
                    'description' => $item['detail'],
                    'nutrition_data' => [
                        'source' => 'seed',
                        'market' => 'cambodia',
                        'sell_by' => $item['unit'] === 'kg' ? ['kg'] : ['qty'],
                        'catalog_category' => $categorySlug,
                    ],
                    'shelf_life_days' => $this->defaultShelfLifeDays($item['type']),
                ];

                $product = Product::withTrashed()->firstWhere('slug', $slug);

                if ($product) {
                    $product->update($attributes);

                    if ($product->trashed()) {
                        $product->restore();
                    }

                    continue;
                }

                Product::query()->create(array_merge(['slug' => $slug], $attributes));
            }
        }
    }

    private function defaultShelfLifeDays(string $type): int
    {
        if ($type === 'staple') {
            return 180;
        }

        if ($type === 'protein') {
            return 3;
        }

        return 5;
    }
}
