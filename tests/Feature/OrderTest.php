<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CommissionFee;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderType;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => UserStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
            ['id' => UserStatus::DELETED_ID, 'name_en' => 'Deleted', 'name_km' => 'បានលុប'],
            ['id' => UserStatus::PENDING_ID, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
        ], ['id'], ['name_en', 'name_km']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'name_en' => 'User', 'name_km' => 'អ្នកប្រើប្រាស់'],
            ['id' => UserType::VENDOR_ID, 'name_en' => 'Vendor', 'name_km' => 'អ្នកលក់'],
            ['id' => UserType::ADMIN_ID, 'name_en' => 'Admin', 'name_km' => 'អ្នកគ្រប់គ្រង'],
        ], ['id'], ['name_en', 'name_km']);

        OrderStatus::upsert([
            ['id' => OrderStatus::PENDING_ID, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
            ['id' => OrderStatus::CONFIRMED_ID, 'name_en' => 'Confirmed', 'name_km' => 'បានបញ្ជាក់'],
            ['id' => OrderStatus::PREPARING_ID, 'name_en' => 'Preparing', 'name_km' => 'កំពុងរៀបចំ'],
            ['id' => OrderStatus::DELIVERED_ID, 'name_en' => 'Delivered', 'name_km' => 'បានដឹកជញ្ជូន'],
            ['id' => OrderStatus::CANCELLED_ID, 'name_en' => 'Cancelled', 'name_km' => 'បានបោះបង់'],
            ['id' => OrderStatus::AWAITING_PAYMENT_ID, 'name_en' => 'Awaiting Payment', 'name_km' => 'រង់ចាំការទូទាត់'],
            ['id' => OrderStatus::OUT_FOR_DELIVERY_ID, 'name_en' => 'Out for Delivery', 'name_km' => 'កំពុងដឹកជញ្ជូន'],
        ], ['id'], ['name_en', 'name_km']);

        OrderType::upsert([
            ['id' => OrderType::STANDARD_ID, 'name_en' => 'Standard', 'name_km' => 'ស្តង់ដារ'],
        ], ['id'], ['name_en', 'name_km']);

        PaymentStatus::upsert([
            ['id' => PaymentStatus::PENDING_ID, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
            ['id' => PaymentStatus::COMPLETED_ID, 'name_en' => 'Completed', 'name_km' => 'បានបញ្ចប់'],
            ['id' => PaymentStatus::FAILED_ID, 'name_en' => 'Failed', 'name_km' => 'បានបរាជ័យ'],
            ['id' => PaymentStatus::REFUNDED_ID, 'name_en' => 'Refunded', 'name_km' => 'បានសងប្រាក់'],
        ], ['id'], ['name_en', 'name_km']);

        Currency::upsert([
            ['id' => Currency::KHR_ID, 'code' => Currency::KHR, 'name_en' => 'Cambodian Riel', 'name_km' => 'រៀល', 'symbol' => '៛'],
            ['id' => Currency::USD_ID, 'code' => Currency::USD, 'name_en' => 'US Dollar', 'name_km' => 'ដុល្លារ', 'symbol' => '$'],
        ], ['id'], ['code', 'name_en', 'name_km', 'symbol']);

        CommissionFee::create([
            'id' => CommissionFee::ID,
            'rate' => '5.00',
            'description' => 'Default commission fee',
        ]);

        ExchangeRate::create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4000.00',
        ]);
        ExchangeRate::create([
            'from_currency_id' => Currency::KHR_ID,
            'to_currency_id' => Currency::USD_ID,
            'rate' => '0.00025',
        ]);
    }

    private function createOrder(User $user, int $orderStatusId = OrderStatus::PENDING_ID, array $overrides = []): Order
    {
        $vendor = User::factory()->create(['user_type_id' => UserType::VENDOR_ID]);
        $address = $user->addresses()->create([
            'label' => 'Home',
            'recipient_name' => 'Test User',
            'phone' => '012345678',
            'address_line_1' => '123 Test St',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'postal_code' => '12000',
            'lat' => 11.5564,
            'long' => 104.9282,
        ]);

        return Order::create(array_merge([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'address_id' => $address->id,
            'order_type_id' => OrderType::STANDARD_ID,
            'order_status_id' => $orderStatusId,
            'payment_status_id' => PaymentStatus::PENDING_ID,
            'currency_id' => Currency::USD_ID,
            'delivery_date' => now()->addDays(3),
            'delivery_slot' => '09:00-12:00',
            'subtotal' => '100.00',
            'discount_amount' => '0.00',
            'delivery_fee' => '5.00',
            'tax_amount' => '10.00',
            'total_amount' => '115.00',
        ], $overrides));
    }

    public function test_list_orders_requires_authentication(): void
    {
        $this->getJson('/api/v1/orders')->assertUnauthorized();
    }

    public function test_user_can_list_their_orders(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createOrder($user);
        $this->createOrder($user);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_user_sees_only_their_own_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->createOrder($user);
        $this->createOrder($user);
        $this->createOrder($otherUser);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $order = $this->createOrder($user);

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/v1/orders/{$order->id}")->assertNotFound();
    }

    public function test_user_can_view_own_order_detail(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = $this->createOrder($user);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'payment_status',
                    'type',
                    'items',
                ],
            ]);
    }

    public function test_user_can_cancel_pending_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = $this->createOrder($user, OrderStatus::PENDING_ID);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status_id' => OrderStatus::CANCELLED_ID,
        ]);
    }

    public function test_user_cannot_cancel_non_pending_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = $this->createOrder($user, OrderStatus::CONFIRMED_ID);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_user_cannot_cancel_others_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $order = $this->createOrder($user, OrderStatus::PENDING_ID);

        Sanctum::actingAs($otherUser);

        $this->postJson("/api/v1/orders/{$order->id}/cancel")->assertNotFound();
    }

    public function test_user_can_confirm_receipt(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = $this->createOrder($user, OrderStatus::PREPARING_ID);

        $response = $this->postJson("/api/v1/orders/{$order->id}/confirm-receipt");

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status_id' => OrderStatus::DELIVERED_ID,
        ]);
    }

    public function test_user_cannot_confirm_receipt_for_pending_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $order = $this->createOrder($user, OrderStatus::PENDING_ID);

        $response = $this->postJson("/api/v1/orders/{$order->id}/confirm-receipt");

        $response->assertStatus(422);
    }

    public function test_batch_pay_requires_authentication(): void
    {
        $this->postJson('/api/v1/orders/batch-pay', [
            'order_ids' => [1],
            'wallet_id' => 1,
            'pin' => '1234',
        ])->assertUnauthorized();
    }

    public function test_batch_pay_creates_payments_for_multiple_orders(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency_id' => Currency::USD_ID,
            'balance' => '500.00',
        ]);

        $user->userProfile()->create([
            'pin' => Hash::make('1234'),
            'locale' => 'en',
            'theme' => 'light',
        ]);

        Sanctum::actingAs($user);

        $order1 = $this->createOrder($user, OrderStatus::AWAITING_PAYMENT_ID);
        $order2 = $this->createOrder($user, OrderStatus::AWAITING_PAYMENT_ID);

        $response = $this->postJson('/api/v1/orders/batch-pay', [
            'order_ids' => [$order1->id, $order2->id],
            'wallet_id' => $wallet->id,
            'pin' => '1234',
        ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $order1->id,
            'payment_status_id' => PaymentStatus::COMPLETED_ID,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order2->id,
            'payment_status_id' => PaymentStatus::COMPLETED_ID,
        ]);
    }

    public function test_batch_pay_fails_with_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency_id' => Currency::USD_ID,
            'balance' => '10.00',
        ]);

        $user->userProfile()->create([
            'pin' => Hash::make('1234'),
            'locale' => 'en',
            'theme' => 'light',
        ]);

        Sanctum::actingAs($user);

        $order = $this->createOrder($user, OrderStatus::AWAITING_PAYMENT_ID);

        $response = $this->postJson('/api/v1/orders/batch-pay', [
            'order_ids' => [$order->id],
            'wallet_id' => $wallet->id,
            'pin' => '1234',
        ]);

        $response->assertStatus(422);
    }
}
