<?php

declare(strict_types=1);

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
        $vendors = [
            ['name' => 'Sok Vannak', 'email' => 'sok.vannak@vendor.local', 'phone' => '+85510111001'],
            ['name' => 'Dara Chenda', 'email' => 'dara.chenda@vendor.local', 'phone' => '+85510111002'],
            ['name' => 'Pisey Rith', 'email' => 'pisey.rith@vendor.local', 'phone' => '+85510111003'],
            ['name' => 'Srey Leak', 'email' => 'srey.leak@vendor.local', 'phone' => '+85510111004'],
            ['name' => 'Kosal Mony', 'email' => 'kosal.mony@vendor.local', 'phone' => '+85510111005'],
            ['name' => 'Nary Sokha', 'email' => 'nary.sokha@vendor.local', 'phone' => '+85510111006'],
            ['name' => 'Vichea Lon', 'email' => 'vichea.lon@vendor.local', 'phone' => '+85510111007'],
            ['name' => 'Rina Kim', 'email' => 'rina.kim@vendor.local', 'phone' => '+85510111008'],
            ['name' => 'Savuth Keo', 'email' => 'savuth.keo@vendor.local', 'phone' => '+85510111009'],
            ['name' => 'Malis Neth', 'email' => 'malis.neth@vendor.local', 'phone' => '+85510111010'],
            ['name' => 'Bopha Pen', 'email' => 'bopha.pen@vendor.local', 'phone' => '+85510111011'],
            ['name' => 'Chan Thy', 'email' => 'chan.thy@vendor.local', 'phone' => '+85510111012'],
            ['name' => 'Sophy Meas', 'email' => 'sophy.meas@vendor.local', 'phone' => '+85510111013'],
            ['name' => 'Ratha Chea', 'email' => 'ratha.chea@vendor.local', 'phone' => '+85510111014'],
            ['name' => 'Kunthea Phan', 'email' => 'kunthea.phan@vendor.local', 'phone' => '+85510111015'],
        ];

        foreach ($vendors as $vendor) {
            $names = preg_split('/\s+/', trim($vendor['name'])) ?: [];
            $firstName = $names[0] ?? $vendor['name'];
            $lastName = count($names) > 1 ? implode(' ', array_slice($names, 1)) : 'Vendor';

            $user = User::query()->updateOrCreate(
                ['email' => $vendor['email']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone_number' => $vendor['phone'],
                    'password' => Hash::make('password'),
                    'user_type_id' => UserType::VENDOR,
                    'user_status_id' => UserStatus::ACTIVE,
                ]
            );

            $user->vendorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $vendor['name'].' Organic Store',
                    'contact_phone' => $vendor['phone'],
                    'city' => 'Phnom Penh',
                    'province' => 'Phnom Penh',
                    'address' => 'FreshLeaf Vendor Cluster',
                    'is_verified' => true,
                    'meta' => ['seeded' => true],
                ]
            );
        }
    }
}
