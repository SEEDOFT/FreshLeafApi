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
            ['id' => UserType::CONSUMER, 'code' => 'consumer', 'name' => 'Consumer'],
            ['id' => UserType::OPERATION, 'code' => 'operation', 'name' => 'Operation'],
            ['id' => UserType::ADMIN, 'code' => 'admin', 'name' => 'Admin'],
        ];

        foreach ($types as $type) {
            UserType::updateOrCreate(
                ['id' => $type['id']],
                ['code' => $type['code'], 'name' => $type['name']]
            );
        }
    }
}
