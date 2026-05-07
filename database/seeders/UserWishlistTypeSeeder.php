<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistType;
use Illuminate\Database\Seeder;

class UserWishlistTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'default', 'name' => 'Default'],
            ['id' => 2, 'code' => 'shared', 'name' => 'Shared'],
        ];

        foreach ($items as $item) {
            UserWishlistType::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
