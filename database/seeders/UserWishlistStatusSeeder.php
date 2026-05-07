<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistStatus;
use Illuminate\Database\Seeder;

class UserWishlistStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'active', 'name' => 'Active'],
            ['id' => 2, 'code' => 'archived', 'name' => 'Archived'],
        ];

        foreach ($items as $item) {
            UserWishlistStatus::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
