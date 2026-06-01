<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $categoryIdsBySlug = ProductCategory::query()->pluck('id', 'slug');
            $unitIdsBySymbol = Unit::query()->pluck('id', 'symbol');
            $typeIdsByName = ProductType::query()->pluck('id', 'name_en');

            $catalog = [
                'leafy-vegetables' => [
                    ['name_en' => 'Morning Glory', 'name_km' => 'ត្រកួន', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'The most popular leafy green in Cambodia, often stir-fried with garlic.', 'detail_km' => 'បន្លែស្លឹកដែលពេញនិយមនៅកម្ពុជា', 'image_url' => 'images/products/morning_glory.png'],
                    ['name_en' => 'Bok Choy', 'name_km' => 'ស្ពៃចង្កឹះ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'A crisp and sweet leafy vegetable widely used in soups and stir-fries.', 'detail_km' => 'ស្ពៃសម្រាប់ស្លនិងឆា', 'image_url' => 'images/products/bok_choy.png'],
                ],
                'fruit-vegetables' => [
                    ['name_en' => 'Eggplant', 'name_km' => 'ត្រប់', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'Commonly used in Cambodian stews (Samlor) or grilled.', 'detail_km' => 'ត្រប់វែងស្រស់', 'image_url' => 'images/products/eggplant.png'],
                    ['name_en' => 'Bitter Gourd', 'name_km' => 'ម្រះ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'Used in soups, often stuffed with minced pork.', 'detail_km' => 'ម្រះស្រស់', 'image_url' => 'images/products/bitter_gourd.png'],
                ],
                'root-and-tuber-vegetables' => [
                    ['name_en' => 'Sweet Potato', 'name_km' => 'ដំឡូងជ្វា', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'A sweet root vegetable used in desserts or eaten boiled.', 'detail_km' => 'ដំឡូងជ្វាស្រស់', 'image_url' => 'images/products/sweet_potato.png'],
                    ['name_en' => 'Taro', 'name_km' => 'ត្រាវ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'A starchy tuber used in savory dishes and traditional Khmer desserts.', 'detail_km' => 'ត្រាវ', 'image_url' => 'images/products/taro.png'],
                ],
                'bulb-and-stem-vegetables' => [
                    ['name_en' => 'Lemongrass', 'name_km' => 'ស្លឹកគ្រៃ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'The foundational aromatic stem used in Kroeung (Khmer curry paste).', 'detail_km' => 'ស្លឹកគ្រៃ', 'image_url' => 'images/products/lemongrass.png'],
                    ['name_en' => 'Lotus Stem', 'name_km' => 'ព្រលិត', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'Crunchy stems often used in sour soups (Samlor Machou).', 'detail_km' => 'ព្រលិតស្រស់', 'image_url' => 'images/products/lotus_stem.png'],
                ],
                'legume-vegetables' => [
                    ['name_en' => 'Long Bean', 'name_km' => 'សណ្តែកកួរ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'Long, crisp beans used in Somlor or eaten raw with Prahok.', 'detail_km' => 'សណ្តែកកួរស្រស់', 'image_url' => 'images/products/long_bean.png'],
                    ['name_en' => 'Winged Bean', 'name_km' => 'ប្រពាយ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'A unique jagged-edge bean with a crunchy texture.', 'detail_km' => 'ប្រពាយ', 'image_url' => 'images/products/winged_bean.png'],
                ],
                'indigenous-and-wild-vegetables' => [
                    ['name_en' => 'Moringa Leaves', 'name_km' => 'ម្រុំ', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'Highly nutritious indigenous leaves often cooked in healthy soups.', 'detail_km' => 'ម្រុំ', 'image_url' => 'images/products/moringa_leaves.png'],
                    ['name_en' => 'Water Mimosa', 'name_km' => 'កញ្ឆែត', 'unit' => 'kg', 'type' => 'Fresh Produce', 'detail_en' => 'An aquatic plant with a spongy stem, popular in stir-fries.', 'detail_km' => 'កញ្ឆែត', 'image_url' => 'images/products/water_mimosa.png'],
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

                    // Ensure ProductType exists
                    $typeNameEn = $item['type'];
                    if (! isset($typeIdsByName[$typeNameEn])) {
                        $type = ProductType::firstOrCreate(
                            ['name_en' => $typeNameEn],
                            ['name_km' => $typeNameEn === 'Fresh Produce' ? 'ផលិតផលស្រស់' : $typeNameEn]
                        );
                        $typeIdsByName[$typeNameEn] = $type->id;
                    }
                    $typeId = $typeIdsByName[$typeNameEn];

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
                        'image_url' => $item['image_url'] ?? null,
                        'nutrition_data' => [
                            'source' => 'seed',
                            'market' => 'cambodia',
                            'sell_by' => $item['unit'] === 'kg' ? ['kg'] : ['qty'],
                            'catalog_category' => $categorySlug,
                        ],
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
        });
    }
}
