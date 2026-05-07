<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserCartType;
use Illuminate\Database\Seeder;

class UserCartTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['id' => 1, 'code' => 'default', 'name' => 'Default'],
            ['id' => 2, 'code' => 'scheduled', 'name' => 'Scheduled'],
        ];

        foreach ($items as $item) {
            UserCartType::query()->updateOrCreate(['id' => $item['id']], ['code' => $item['code'], 'name' => $item['name']]);
        }
    }
}
