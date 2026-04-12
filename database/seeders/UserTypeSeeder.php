<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => UserType::CONSUMER, 'name' => 'Consumer'],
            ['id' => UserType::VENDOR, 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'name' => 'Admin'],
        ];

        foreach ($types as $type) {
            UserType::updateOrCreate(
                ['id' => $type['id']],
                ['name' => $type['name']]
            );
        }
    }
}
