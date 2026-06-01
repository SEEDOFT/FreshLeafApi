<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        
        $users = [
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'phone_number' => '+85581968185',
                'password' => $password,
                'user_type_id' => UserType::ADMIN_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Vendor',
                'phone_number' => '+85581968185',
                'password' => $password,
                'user_type_id' => UserType::VENDOR_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Consumer',
                'phone_number' => '+85581968185',
                'password' => $password,
                'user_type_id' => UserType::CONSUMER_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                [
                    'phone_number' => $userData['phone_number'],
                    'user_type_id' => $userData['user_type_id']
                ],
                $userData
            );
        }
    }
}
