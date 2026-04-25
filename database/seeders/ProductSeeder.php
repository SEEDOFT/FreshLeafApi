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
        $typeIdsByName = ProductType::query()->pluck('id', 'name');

        $catalog = [
            'leafy-vegetables' => [
                ['name' => 'Morning Glory', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Popular stir-fry leafy vegetable in Cambodia'],
                ['name' => 'Bok Choy', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Crunchy leafy vegetable for soups and stir-fry'],
                ['name' => 'Chinese Kale', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Firm leaves and stems, common in local markets'],
                ['name' => 'Water Spinach Bunch', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Sold by bunch quantity for quick retail turnover'],
                ['name' => 'Lettuce Head', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Sold per head for salad and fresh serving'],
                ['name' => 'Mustard Green', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Suitable for soup and salted vegetable dishes'],
            ],
            'fruiting-vegetables' => [
                ['name' => 'Tomato', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Core ingredient for sauces and soups'],
                ['name' => 'Cucumber', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Fresh salad vegetable, high daily demand'],
                ['name' => 'Eggplant', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Used in soups and grilled dishes'],
                ['name' => 'Bitter Melon', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Traditional bitter flavor vegetable'],
                ['name' => 'Pumpkin', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Sold per fruit for household and restaurant use'],
                ['name' => 'Bottle Gourd', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Commonly sold per piece in fresh markets'],
            ],
            'root-vegetables' => [
                ['name' => 'Carrot', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Used in stir-fry, soup, and salad'],
                ['name' => 'White Radish', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Popular in soups and pickled dishes'],
                ['name' => 'Sweet Potato', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Root crop with stable demand'],
                ['name' => 'Cassava Root', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Used for boiling, steaming, and flour processing'],
                ['name' => 'Turmeric Root', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Cooking and medicinal aromatic root'],
                ['name' => 'Galangal Root', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Essential aromatic root in Khmer cuisine'],
            ],
            'herbs-aromatic-plants' => [
                ['name' => 'Lemongrass', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Aromatic herb sold as stalk bundles'],
                ['name' => 'Kaffir Lime Leaves', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Fragrant leaves used in soup and curry'],
                ['name' => 'Holy Basil', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Fresh aromatic herb sold in bunches'],
                ['name' => 'Mint', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Used in fresh rolls and salads'],
                ['name' => 'Coriander', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Garnish and seasoning herb'],
                ['name' => 'Spring Onion', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Daily cooking aromatic sold by bundle'],
            ],
            'legumes' => [
                ['name' => 'Long Bean', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Main legume in Cambodian stir-fry dishes'],
                ['name' => 'Yardlong Bean', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'High-volume fresh market legume'],
                ['name' => 'Green Bean', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Short-cycle vegetable with steady demand'],
                ['name' => 'Snow Pea', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Crisp legume for premium menu items'],
                ['name' => 'Soybean Sprout Pack', 'unit' => 'qty', 'type' => 'Fresh Produce', 'detail' => 'Ready-to-cook sprouts sold by pack quantity'],
                ['name' => 'Peanut Fresh Pod', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail' => 'Local snack and ingredient legume'],
            ],
        ];

        $defaultStatusId = ProductStatus::ACTIVE;
        $fallbackUnitId = $unitIdsBySymbol['KG'] ?? null;
        $fallbackTypeId = $typeIdsByName['Fresh Produce'] ?? null;

        foreach ($catalog as $categorySlug => $items) {
            $categoryId = $categoryIdsBySlug[$categorySlug] ?? null;

            if (! $categoryId) {
                continue;
            }

            foreach ($items as $item) {
                $unitId = $unitIdsBySymbol[$item['unit']] ?? $fallbackUnitId;
                $typeId = $typeIdsByName[$item['type']] ?? $fallbackTypeId;

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
