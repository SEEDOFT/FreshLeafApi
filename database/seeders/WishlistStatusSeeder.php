<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WishlistStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WishlistStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(static function () {
            $types = [
                [
                    'id' => WishlistStatus::ACTIVE_ID,
                    'name_en' => 'Active',
                    'name_km' => 'សកម្ម',
                ],
                [
                    'id' => WishlistStatus::DELETED_ID,
                    'name_en' => 'Deleted',
                    'name_km' => 'បានលុប',
                ],
            ];

            foreach ($types as $type) {
                WishlistStatus::create($type);
            }
        });
    }
}
