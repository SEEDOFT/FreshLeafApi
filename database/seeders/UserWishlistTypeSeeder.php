<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserWishlistType;
use Illuminate\Database\Seeder;

class UserWishlistTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'code' => 'DEFAULT',
                'name_en' => 'Default',
                'name_km' => 'លំនាំដើម',
            ],
            [
                'id' => 2,
                'code' => 'FAVORITE',
                'name_en' => 'Favorite',
                'name_km' => 'ចំណូលចិត្ត',
            ],
        ];

        foreach ($types as $type) {
            UserWishlistType::create($type);
        }
    }
}
