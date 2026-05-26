<?php

declare(strict_types=1);

namespace Tests\Feature\Api\User;

use App\Models\Cart;
use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_to_cart_and_checkout(): void
    {
        $this->seed(); // Seed basic data like status, types, etc.
        $user = User::factory()->create();
        $user->ensureDefaultPaymentMethod();

        $inventory = VendorInventory::factory()->create(['stock_quantity' => '100.00']);

        // Empties existing carts
        Cart::truncate();

        $response = $this->actingAs($user)->postJson('/api/v1/cart', [
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '1',
        ]);

        $response->dump();
        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '1.00',
        ]);

        $checkoutResponse = $this->actingAs($user)->postJson('/api/v1/cart/checkout', [
            'address_id' => 1,
            'payment_method_type_code' => 'wallet',
            'order_type_id' => 1,
        ]);

        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertJsonPath('data.address_id', 1);
    }
}
