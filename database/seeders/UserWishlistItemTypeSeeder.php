<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistItemType;
use Illuminate\Database\Seeder;

class UserWishlistItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'default', 'name' => 'Default'],
        ];

        foreach ($items as $item) {
            UserWishlistItemType::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
