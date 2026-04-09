<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operationUsers = [
            ['first_name' => 'Sok', 'last_name' => 'Vannak', 'email' => 'sok.vannak@vendor.local', 'phone_number' => '+85510111001'],
            ['first_name' => 'Dara', 'last_name' => 'Chenda', 'email' => 'dara.chenda@vendor.local', 'phone_number' => '+85510111002'],
            ['first_name' => 'Pisey', 'last_name' => 'Rith', 'email' => 'pisey.rith@vendor.local', 'phone_number' => '+85510111003'],
            ['first_name' => 'Srey', 'last_name' => 'Leak', 'email' => 'srey.leak@vendor.local', 'phone_number' => '+85510111004'],
            ['first_name' => 'Kosal', 'last_name' => 'Mony', 'email' => 'kosal.mony@vendor.local', 'phone_number' => '+85510111005'],
            ['first_name' => 'Nary', 'last_name' => 'Sokha', 'email' => 'nary.sokha@vendor.local', 'phone_number' => '+85510111006'],
            ['first_name' => 'Vichea', 'last_name' => 'Lon', 'email' => 'vichea.lon@vendor.local', 'phone_number' => '+85510111007'],
            ['first_name' => 'Rina', 'last_name' => 'Kim', 'email' => 'rina.kim@vendor.local', 'phone_number' => '+85510111008'],
            ['first_name' => 'Savuth', 'last_name' => 'Keo', 'email' => 'savuth.keo@vendor.local', 'phone_number' => '+85510111009'],
            ['first_name' => 'Malis', 'last_name' => 'Neth', 'email' => 'malis.neth@vendor.local', 'phone_number' => '+85510111010'],
            ['first_name' => 'Bopha', 'last_name' => 'Pen', 'email' => 'bopha.pen@vendor.local', 'phone_number' => '+85510111011'],
            ['first_name' => 'Chan', 'last_name' => 'Thy', 'email' => 'chan.thy@vendor.local', 'phone_number' => '+85510111012'],
            ['first_name' => 'Sophy', 'last_name' => 'Meas', 'email' => 'sophy.meas@vendor.local', 'phone_number' => '+85510111013'],
            ['first_name' => 'Ratha', 'last_name' => 'Chea', 'email' => 'ratha.chea@vendor.local', 'phone_number' => '+85510111014'],
            ['first_name' => 'Kunthea', 'last_name' => 'Phan', 'email' => 'kunthea.phan@vendor.local', 'phone_number' => '+85510111015'],
        ];

        foreach ($operationUsers as $operationUser) {
            User::query()->updateOrCreate(
                ['email' => $operationUser['email']],
                [
                    'first_name' => $operationUser['first_name'],
                    'last_name' => $operationUser['last_name'],
                    'phone_number' => $operationUser['phone_number'],
                    'password' => Hash::make('password'),
                    'user_type_id' => UserType::OPERATION,
                    'user_status_id' => UserStatus::ACTIVE,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
