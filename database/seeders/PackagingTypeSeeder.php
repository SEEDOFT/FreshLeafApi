<?php

namespace Database\Seeders;

use App\Models\PackagingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackagingTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(static function () {
            $types = [
                ['name_en' => 'Vacuum sealed plastic', 'name_km' => 'ផ្លាស្ទិកបិទជិតខ្យល់'],
                ['name_en' => 'Cardboard box', 'name_km' => 'ប្រអប់ក្រដាស'],
                ['name_en' => 'Biodegradable wrapper', 'name_km' => 'ការវេចខ្ចប់បែបធម្មជាតិ'],
                ['name_en' => 'Glass jar', 'name_km' => 'ដបកែវ'],
                ['name_en' => 'Plastic crate', 'name_km' => 'កន្ត្រកជ័រ'],
                ['name_en' => 'Loose / No packaging', 'name_km' => 'មិនមានការវេចខ្ចប់'],
            ];

            foreach ($types as $type) {
                PackagingType::firstOrCreate(['name_en' => $type['name_en']], $type);
            }
        });
    }
}
