<?php

declare(strict_types=1);

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
            [
                'id' => UserType::ADMIN_ID,
                'name_en' => 'ADMIN',
                'name_km' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
            ],
            [
                'id' => UserType::VENDOR_ID,
                'name_en' => 'VENDOR',
                'name_km' => 'អ្នកលក់',
            ],
            [
                'id' => UserType::CONSUMER_ID,
                'name_en' => 'CONSUMER',
                'name_km' => 'អ្នកប្រើប្រាស់',
            ],
        ];

        foreach ($types as $type) {
            UserType::create($type);
        }
    }
}
