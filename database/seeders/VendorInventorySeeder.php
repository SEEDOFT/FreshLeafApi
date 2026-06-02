<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\User;
use App\Models\UserType;
use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            // Get the default vendor (Test Vendor)
            $vendor = User::where('user_type_id', UserType::VENDOR_ID)
                ->where('first_name', 'Test')
                ->where('last_name', 'Vendor')
                ->first();

            if (! $vendor) {
                return;
            }

            // Get existing seeded products
            $products = Product::all();
            $packagingTypes = PackagingType::pluck('id')->toArray();
            $provinces = ['Phnom Penh', 'Siem Reap', 'Battambang', 'Kampot', 'Kandal', 'Kampong Cham', 'Takeo'];
            $certifications = ['Organic', 'GAP', 'None', 'Local Standard', 'PGS'];

            foreach ($products as $product) {
                VendorInventory::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'currency_id' => Currency::USD_ID,
                        'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
                        'price' => rand(10, 50) / 10,
                        'stock_quantity' => rand(50, 200),
                        'unit_id' => $product->default_unit_id,
                        'harvest_date' => now()->subDays(rand(1, 3)),
                        'farm_location' => 'Farm '.rand(1, 100),
                        'province_of_origin' => $provinces[array_rand($provinces)],
                        'certification_type' => $certifications[array_rand($certifications)],
                        'packaging_type_id' => ! empty($packagingTypes) ? $packagingTypes[array_rand($packagingTypes)] : null,
                        'shelf_life_days' => rand(3, 14),
                        'batch_images' => ['images/batch/default_1.png', 'images/batch/default_2.png'],
                    ]
                );
            }
        });
    }
}
