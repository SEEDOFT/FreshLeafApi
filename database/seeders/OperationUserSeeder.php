<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorStatus;
use App\Models\VendorType;
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
            Vendor::query()->updateOrCreate(
                ['email' => $vendor['email']],
                [
                    'name' => $vendor['name'],
                    'password' => Hash::make('password'),
                    'type_id' => VendorType::STANDART,
                    'status_id' => VendorStatus::ACTIVE,
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
