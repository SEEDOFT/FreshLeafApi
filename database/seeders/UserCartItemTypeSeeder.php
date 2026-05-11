<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartItemType;
use Illuminate\Database\Seeder;

class UserCartItemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'id' => 1,
                'code' => 'standard',
                'name' => 'Standard',
            ],
            [
                'id' => 2,
                'code' => 'subscription',
                'name' => 'Subscription',
            ],
        ];

        foreach ($items as $item) {
            UserCartItemType::create($item);
        }
    }
}
