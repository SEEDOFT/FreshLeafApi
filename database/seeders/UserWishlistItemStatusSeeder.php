<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistItemStatus;
use Illuminate\Database\Seeder;

class UserWishlistItemStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'active', 'name' => 'Active'],
            ['id' => 2, 'code' => 'inactive', 'name' => 'Inactive'],
        ];

        foreach ($items as $item) {
            UserWishlistItemStatus::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
