<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CommissionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'commission_rate_percentage'],
            [
                'value' => '10.00',
                'type' => 'decimal',
                'group' => 'financial',
            ]
        );
    }
}
