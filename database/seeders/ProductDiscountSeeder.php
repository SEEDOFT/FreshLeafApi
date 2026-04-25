<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDiscount;
use Illuminate\Database\Seeder;

class ProductDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()->take(5)->get();

        foreach ($products as $index => $product) {
            $percentage = [10, 15, 20, 25, 50][$index % 5];

            ProductDiscount::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'discount_percentage' => $percentage,
                    'is_active' => true,
                    'starts_at' => now()->subDays(2),
                    'ends_at' => now()->addDays(7),
                ]
            );
        }
    }
}
