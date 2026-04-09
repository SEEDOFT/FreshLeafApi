<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Phnom Penh Fresh Hub', 'contact_name' => 'Sok Vannak', 'phone' => '+85510111001', 'email' => 'supply.pp.freshhub@vendor.local', 'address' => 'Phnom Penh, Cambodia'],
            ['name' => 'Kandal Green Source', 'contact_name' => 'Dara Chenda', 'phone' => '+85510111002', 'email' => 'supply.kandal.green@vendor.local', 'address' => 'Kandal, Cambodia'],
            ['name' => 'Takeo Produce Network', 'contact_name' => 'Pisey Rith', 'phone' => '+85510111003', 'email' => 'supply.takeo.produce@vendor.local', 'address' => 'Takeo, Cambodia'],
            ['name' => 'Battambang Farm Line', 'contact_name' => 'Srey Leak', 'phone' => '+85510111004', 'email' => 'supply.btb.farmline@vendor.local', 'address' => 'Battambang, Cambodia'],
            ['name' => 'Siem Reap Agro Trade', 'contact_name' => 'Kosal Mony', 'phone' => '+85510111005', 'email' => 'supply.sr.agro@vendor.local', 'address' => 'Siem Reap, Cambodia'],
            ['name' => 'Kampot Herb Collective', 'contact_name' => 'Nary Sokha', 'phone' => '+85510111006', 'email' => 'supply.kampot.herb@vendor.local', 'address' => 'Kampot, Cambodia'],
            ['name' => 'Kampong Cham Daily Supply', 'contact_name' => 'Vichea Lon', 'phone' => '+85510111007', 'email' => 'supply.kc.daily@vendor.local', 'address' => 'Kampong Cham, Cambodia'],
            ['name' => 'Prey Veng Fresh Basket', 'contact_name' => 'Rina Kim', 'phone' => '+85510111008', 'email' => 'supply.pv.basket@vendor.local', 'address' => 'Prey Veng, Cambodia'],
            ['name' => 'Svay Rieng Market Link', 'contact_name' => 'Savuth Keo', 'phone' => '+85510111009', 'email' => 'supply.sv.market@vendor.local', 'address' => 'Svay Rieng, Cambodia'],
            ['name' => 'Banteay Meanchey Food Chain', 'contact_name' => 'Malis Neth', 'phone' => '+85510111010', 'email' => 'supply.bm.foodchain@vendor.local', 'address' => 'Banteay Meanchey, Cambodia'],
            ['name' => 'Pursat Harvest Supply', 'contact_name' => 'Bopha Pen', 'phone' => '+85510111011', 'email' => 'supply.pursat.harvest@vendor.local', 'address' => 'Pursat, Cambodia'],
            ['name' => 'Sihanoukville Sea and Farm', 'contact_name' => 'Chan Thy', 'phone' => '+85510111012', 'email' => 'supply.shv.seafarm@vendor.local', 'address' => 'Sihanoukville, Cambodia'],
            ['name' => 'Kep Coastal Foods', 'contact_name' => 'Sophy Meas', 'phone' => '+85510111013', 'email' => 'supply.kep.coastal@vendor.local', 'address' => 'Kep, Cambodia'],
            ['name' => 'Koh Kong Fresh Channel', 'contact_name' => 'Ratha Chea', 'phone' => '+85510111014', 'email' => 'supply.kk.channel@vendor.local', 'address' => 'Koh Kong, Cambodia'],
            ['name' => 'Kampong Speu Agro Point', 'contact_name' => 'Kunthea Phan', 'phone' => '+85510111015', 'email' => 'supply.ks.agropoint@vendor.local', 'address' => 'Kampong Speu, Cambodia'],
        ];

        $emails = array_map(
            static fn (array $supplier): string => $supplier['email'],
            $suppliers
        );

        Supplier::query()->whereNotIn('email', $emails)->delete();

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['email' => $supplier['email']],
                [
                    'name' => $supplier['name'],
                    'contact_name' => $supplier['contact_name'],
                    'phone' => $supplier['phone'],
                    'address' => $supplier['address'],
                ]
            );
        }
    }
}
