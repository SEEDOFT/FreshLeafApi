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
        $typeIdsByName = ProductType::query()->pluck('id', 'name_en');

        $catalog = [
            'leafy-vegetables' => [
                ['name_en' => 'Morning Glory', 'name_km' => 'ត្រកួន', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Popular stir-fry leafy vegetable in Cambodia', 'detail_km' => 'បន្លែស្លឹកដែលពេញនិយមនៅកម្ពុជា'],
                ['name_en' => 'Bok Choy', 'name_km' => 'ស្ពៃតឿ', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Crunchy leafy vegetable for soups and stir-fry', 'detail_km' => 'ស្ពៃសម្រាប់ស្លនិងឆា'],
                ['name_en' => 'Chinese Kale', 'name_km' => 'ខាត់ណា', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Firm leaves and stems, common in local markets', 'detail_km' => 'ខាត់ណាស្រស់'],
                ['name_en' => 'Lettuce', 'name_km' => 'សាលាដ', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Fresh lettuce for salads', 'detail_km' => 'សាលាដស្រស់សម្រាប់ញ៉ាំឆៅ'],
            ],
            'fruit-vegetables' => [
                ['name_en' => 'Tomato', 'name_km' => 'ប៉េងប៉ោះ', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Core ingredient for sauces and soups', 'detail_km' => 'ប៉េងប៉ោះស្រស់'],
                ['name_en' => 'Cucumber', 'name_km' => 'ត្រសក់', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Fresh salad vegetable, high daily demand', 'detail_km' => 'ត្រសក់ស្រស់'],
                ['name_en' => 'Eggplant', 'name_km' => 'ត្រប់', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Used in soups and grilled dishes', 'detail_km' => 'ត្រប់វែងស្រស់'],
                ['name_en' => 'Bitter Melon', 'name_km' => 'ម្រះ', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Traditional bitter flavor vegetable', 'detail_km' => 'ម្រះស្រស់'],
            ],
            'root-and-tuber-vegetables' => [
                ['name_en' => 'Carrot', 'name_km' => 'ការ៉ុត', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Used in stir-fry, soup, and salad', 'detail_km' => 'ការ៉ុតស្រស់'],
                ['name_en' => 'White Radish', 'name_km' => 'ឆៃថាវ', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Popular in soups and pickled dishes', 'detail_km' => 'ឆៃថាវស្រស់'],
                ['name_en' => 'Sweet Potato', 'name_km' => 'ដំឡូងជ្វា', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Root crop with stable demand', 'detail_km' => 'ដំឡូងជ្វាស្រស់'],
            ],
            'bulb-and-stem-vegetables' => [
                ['name_en' => 'Onion', 'name_km' => 'ខ្ទឹមបារាំង', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Essential bulb vegetable for cooking', 'detail_km' => 'ខ្ទឹមបារាំងស្រស់'],
                ['name_en' => 'Garlic', 'name_km' => 'ខ្ទឹមស', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Aromatic bulb used in mostly all dishes', 'detail_km' => 'ខ្ទឹមស'],
                ['name_en' => 'Celery', 'name_km' => 'ស៊ែលឺរី', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Crunchy stem vegetable', 'detail_km' => 'ស៊ែលឺរីស្រស់'],
            ],
            'legume-vegetables' => [
                ['name_en' => 'Long Bean', 'name_km' => 'សណ្តែកកួរ', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Main legume in Cambodian stir-fry dishes', 'detail_km' => 'សណ្តែកកួរស្រស់'],
                ['name_en' => 'Snow Pea', 'name_km' => 'សណ្តែកបារាំង', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Crisp legume for premium menu items', 'detail_km' => 'សណ្តែកបារាំងស្រស់'],
            ],
            'indigenous-and-wild-vegetables' => [
                ['name_en' => 'Water Lily', 'name_km' => 'ព្រលិត', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Edible stems of water lily', 'detail_km' => 'ព្រលិតស្រស់'],
                ['name_en' => 'Bamboo Shoot', 'name_km' => 'ទំពាំង', 'unit' => 'KG', 'type' => 'Fresh Produce', 'detail_en' => 'Edible shoots of bamboo species', 'detail_km' => 'ទំពាំងស្រស់'],
            ],
        ];

        $defaultStatusId = ProductStatus::PUBLISHED_ID;
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

                $slug = Str::slug($item['name_en']);

                $attributes = [
                    'product_category_id' => $categoryId,
                    'product_type_id' => $typeId,
                    'default_unit_id' => $unitId,
                    'product_status_id' => $defaultStatusId,
                    'name_en' => $item['name_en'],
                    'name_km' => $item['name_km'],
                    'description_en' => $item['detail_en'],
                    'description_km' => $item['detail_km'],
                    'nutrition_data' => [
                        'source' => 'seed',
                        'market' => 'cambodia',
                        'sell_by' => $item['unit'] === 'kg' ? ['kg'] : ['qty'],
                        'catalog_category' => $categorySlug,
                    ],
                    'shelf_life_days' => $this->defaultShelfLifeDays($item['type']),
                    'is_active' => true,
                    'is_organic' => true,
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
