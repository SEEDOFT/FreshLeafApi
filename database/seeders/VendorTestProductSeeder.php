<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use Illuminate\Database\Seeder;

class VendorTestProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed default product type with ID 1
        ProductType::firstOrCreate(
            ['id' => 1],
            [
                'name_en' => 'Fresh Produce',
                'name_km' => 'ផលិតផលស្រស់',
            ]
        );

        // 2. Run the core ProductSeeder to get high-quality realistic produce
        $this->call(ProductSeeder::class);

        // 3. Generate remaining products to reach 50+ total using factory
        $currentCount = Product::count();
        $needed = 50 - $currentCount;

        if ($needed > 0) {
            Product::factory()->count($needed)->create([
                'product_category_id' => fn () => ProductCategory::inRandomOrder()->first()?->id ?? ProductCategory::factory(),
                'product_type_id' => 1,
                'default_unit_id' => null, // Allowed null as per our design change
                'product_status_id' => ProductStatus::PUBLISHED_ID,
            ]);
        }
    }
}
