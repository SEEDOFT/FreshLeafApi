<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistItemType;
use Illuminate\Database\Seeder;

class UserWishlistItemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'code' => 'PRODUCT',
                'name_en' => 'Product',
                'name_km' => 'ផលិតផល',
            ],
            [
                'id' => 2,
                'code' => 'SERVICE',
                'name_en' => 'Service',
                'name_km' => 'សេវាកម្ម',
            ],
        ];

        foreach ($types as $type) {
            UserWishlistItemType::create($type);
        }
    }
}
