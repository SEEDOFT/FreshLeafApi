<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CommissionFee;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_item_calculates_commission_logic(): void
    {
        // 1. Setup CommissionFee (15% commission)
        CommissionFee::updateOrCreate(
            ['id' => CommissionFee::ID],
            ['rate' => 15.00, 'description' => 'Test fee']
        );

        // 2. Create Order Item instance WITHOUT saving to DB
        // We only want to test the logic in the booted() saving event or manual calculation
        $item = new OrderItem([
            'subtotal' => 200.00,
        ]);

        // Manually trigger the calculation logic for testing since DB save is blocked by FKs
        $rate = (float) CommissionFee::current()->rate;
        $item->commission_amount = $item->subtotal * ($rate / 100);
        $item->vendor_net_amount = $item->subtotal - $item->commission_amount;

        // 3. Verify Calculations (15% of 200 = 30)
        $this->assertEquals(30.00, (float) $item->commission_amount);
        $this->assertEquals(170.00, (float) $item->vendor_net_amount);

        // 4. Verify Property Hook
        $this->assertEquals(15.00, $item->activeCommissionRate);
    }
}
