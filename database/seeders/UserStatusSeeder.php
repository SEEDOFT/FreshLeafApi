<?php

namespace Database\Seeders;

use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => UserStatus::ACTIVE, 'code' => 'active', 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'code' => 'inactive', 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'code' => 'deleted', 'name' => 'Deleted'],
        ];

        foreach ($statuses as $status) {
            UserStatus::updateOrCreate(
                ['id' => $status['id']],
                ['code' => $status['code'], 'name' => $status['name']]
            );
        }
    }
}
