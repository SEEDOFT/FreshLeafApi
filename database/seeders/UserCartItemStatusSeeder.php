<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartItemStatus;
use Illuminate\Database\Seeder;

class UserCartItemStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'active', 'name' => 'Active'],
            ['id' => 2, 'code' => 'saved_for_later', 'name' => 'Saved for later'],
        ];

        foreach ($items as $item) {
            UserCartItemStatus::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
