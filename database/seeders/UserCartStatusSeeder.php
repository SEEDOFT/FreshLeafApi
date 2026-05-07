<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartStatus;
use Illuminate\Database\Seeder;

class UserCartStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'active', 'name' => 'Active'],
            ['id' => 2, 'code' => 'abandoned', 'name' => 'Abandoned'],
            ['id' => 3, 'code' => 'converted', 'name' => 'Converted'],
        ];

        foreach ($items as $item) {
            UserCartStatus::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
