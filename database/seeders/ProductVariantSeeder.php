<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitsById = Unit::query()->get()->keyBy('id');
        $products = Product::query()->get();

        foreach ($products as $product) {
            $unit = $unitsById->get($product->default_unit_id);

            if (! $unit) {
                continue;
            }

            $basePrice = $this->basePriceForProduct($product->slug);

            foreach ($this->variantTemplatesForUnit($unit->symbol) as $variantTemplate) {
                ProductVariant::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'name' => $variantTemplate['name'],
                    ],
                    [
                        'unit_id' => $unit->id,
                        'quantity_in_unit' => $variantTemplate['quantity_in_unit'],
                        'price' => round($basePrice * $variantTemplate['price_multiplier'], 2),
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array{name: string, quantity_in_unit: float|int, price_multiplier: float}>
     */
    private function variantTemplatesForUnit(string $symbol): array
    {
        if ($symbol === 'kg') {
            return [
                ['name' => '500 g', 'quantity_in_unit' => 0.5, 'price_multiplier' => 0.60],
                ['name' => '1 kg', 'quantity_in_unit' => 1, 'price_multiplier' => 1.00],
                ['name' => '5 kg', 'quantity_in_unit' => 5, 'price_multiplier' => 4.60],
            ];
        }

        if ($symbol === 'bunch') {
            return [
                ['name' => '1 bunch', 'quantity_in_unit' => 1, 'price_multiplier' => 1.00],
                ['name' => '3 bunches', 'quantity_in_unit' => 3, 'price_multiplier' => 2.80],
            ];
        }

        if ($symbol === 'tray') {
            return [
                ['name' => '1 tray', 'quantity_in_unit' => 1, 'price_multiplier' => 1.00],
                ['name' => '2 trays', 'quantity_in_unit' => 2, 'price_multiplier' => 1.90],
            ];
        }

        if ($symbol === 'piece') {
            return [
                ['name' => '1 piece', 'quantity_in_unit' => 1, 'price_multiplier' => 1.00],
                ['name' => '6 pieces', 'quantity_in_unit' => 6, 'price_multiplier' => 5.60],
            ];
        }

        if ($symbol === 'qty') {
            return [
                ['name' => '1 qty', 'quantity_in_unit' => 1, 'price_multiplier' => 1.00],
                ['name' => '5 qty', 'quantity_in_unit' => 5, 'price_multiplier' => 4.70],
                ['name' => '10 qty', 'quantity_in_unit' => 10, 'price_multiplier' => 9.10],
            ];
        }

        return [
            ['name' => '1 pack', 'quantity_in_unit' => 1, 'price_multiplier' => 1.00],
            ['name' => '5 packs', 'quantity_in_unit' => 5, 'price_multiplier' => 4.50],
        ];
    }

    private function basePriceForProduct(string $slug): float
    {
        $hash = abs(crc32($slug));

        return 1.50 + (($hash % 450) / 100);
    }
}
