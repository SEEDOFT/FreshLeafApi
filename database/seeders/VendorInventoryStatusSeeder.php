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
        $types = [
            [
                'id' => VendorInventoryStatus::AVAILABLE_ID,
                'name_en' => 'Available',
                'name_km' => 'មានលក់',
            ],
            [
                'id' => VendorInventoryStatus::OUT_OF_STOCK_ID,
                'name_en' => 'Out of Stock',
                'name_km' => 'អស់ពីស្តុក',
            ],
            [
                'id' => VendorInventoryStatus::HIDDEN_ID,
                'name_en' => 'Hidden',
                'name_km' => 'លាក់',
            ],
            [
                'id' => VendorInventoryStatus::PENDING_REVIEW_ID,
                'name_en' => 'Pending Review',
                'name_km' => 'រង់ចាំការពិនិត្យ',
            ],
        ];

        foreach ($types as $type) {
            VendorInventoryStatus::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
