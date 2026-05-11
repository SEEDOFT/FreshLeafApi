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
                'id' => 1,
                'code' => 'ADMIN',
                'name_en' => 'ADMIN',
                'name_km' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
            ],
            [
                'id' => 2,
                'code' => 'VENDOR',
                'name_en' => 'VENDOR',
                'name_km' => 'អ្នកលក់',
            ],
            [
                'id' => 3,
                'code' => 'CONSUMER',
                'name_en' => 'CONSUMER',
                'name_km' => 'អ្នកប្រើប្រាស់',
            ],
        ];

        foreach ($types as $type) {
            UserType::create($type);
        }
    }
}
