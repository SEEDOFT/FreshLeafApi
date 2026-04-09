<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryId = ProductCategory::query()->value('id');
        $productTypeId = ProductType::query()->value('id');
        $unitId = Unit::query()->value('id');

        if (! $categoryId || ! $productTypeId || ! $unitId) {
            return;
        }

        $sampleProducts = [
            ['name' => 'Sample Morning Glory', 'shelf_life_days' => 4],
            ['name' => 'Sample Bok Choy', 'shelf_life_days' => 4],
            ['name' => 'Sample Mango', 'shelf_life_days' => 6],
            ['name' => 'Sample Jasmine Rice', 'shelf_life_days' => 365],
            ['name' => 'Sample Fresh Tilapia', 'shelf_life_days' => 2],
        ];

        foreach ($sampleProducts as $sampleProduct) {
            $slug = Str::slug($sampleProduct['name']);

            $attributes = [
                'product_category_id' => $categoryId,
                'product_type_id' => $productTypeId,
                'default_unit_id' => $unitId,
                'product_status_id' => ProductStatus::ACTIVE,
                'name' => $sampleProduct['name'],
                'description' => $sampleProduct['name'].' for API CRUD testing.',
                'nutrition_data' => ['seed' => 'sample'],
                'shelf_life_days' => $sampleProduct['shelf_life_days'],
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
