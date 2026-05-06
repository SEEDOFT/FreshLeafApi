<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\VendorInventoryStatus;
use Illuminate\Database\Seeder;

class VendorInventoryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => VendorInventoryStatus::ACTIVE, 'code' => 'active', 'name' => 'Active'],
            ['id' => VendorInventoryStatus::INACTIVE, 'code' => 'inactive', 'name' => 'Inactive'],
            ['id' => VendorInventoryStatus::OUT_OF_STOCK, 'code' => 'out_of_stock', 'name' => 'Out of Stock'],
        ];

        foreach ($statuses as $status) {
            VendorInventoryStatus::query()->updateOrCreate(
                ['id' => $status['id']],
                ['code' => $status['code'], 'name' => $status['name']]
            );
        }
    }
}
