<?php

namespace Database\Seeders;

use App\Models\PaymentMethodStatus;
use Illuminate\Database\Seeder;

class PaymentMethodStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => PaymentMethodStatus::ACTIVE, 'code' => 'active', 'name' => 'Active'],
            ['id' => PaymentMethodStatus::INACTIVE, 'code' => 'inactive', 'name' => 'Inactive'],
            ['id' => PaymentMethodStatus::DELETE, 'code' => 'deleted', 'name' => 'Deleted'],
        ];

        foreach ($statuses as $status) {
            PaymentMethodStatus::updateOrCreate(
                ['id' => $status['id']],
                ['code' => $status['code'], 'name' => $status['name']]
            );
        }
    }
}
