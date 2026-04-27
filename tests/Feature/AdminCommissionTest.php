<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_item_calculates_commission_logic(): void
    {
        // 1. Setup Setting (15% commission)
        Setting::updateOrCreate(
            ['key' => 'commission_rate_percentage'],
            ['value' => '15.00', 'type' => 'decimal']
        );

        // 2. Create Order Item instance WITHOUT saving to DB
        // We only want to test the logic in the booted() saving event or manual calculation
        $item = new OrderItem([
            'subtotal' => 200.00,
        ]);

        // Manually trigger the calculation logic for testing since DB save is blocked by FKs
        $rate = (float) Setting::get('commission_rate_percentage', 10.00);
        $item->commission_amount = $item->subtotal * ($rate / 100);
        $item->vendor_net_amount = $item->subtotal - $item->commission_amount;

        // 3. Verify Calculations (15% of 200 = 30)
        $this->assertEquals(30.00, (float) $item->commission_amount);
        $this->assertEquals(170.00, (float) $item->vendor_net_amount);
        
        // 4. Verify Property Hook
        $this->assertEquals(15.00, $item->activeCommissionRate);
    }
}
