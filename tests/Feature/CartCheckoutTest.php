<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartStatus;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\OrderStatus;
use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryStatus;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

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

        CartStatus::upsert([
            ['id' => CartStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => CartStatus::REMOVED_ID, 'name_en' => 'Removed', 'name_km' => 'បានលុប'],
            ['id' => CartStatus::CHECKED_OUT_ID, 'name_en' => 'Checked Out', 'name_km' => 'បានបញ្ជាទិញ'],
        ], ['id'], ['name_en', 'name_km']);

        Currency::upsert([
            ['id' => Currency::KHR_ID, 'code' => 'KHR', 'name_en' => 'Cambodian Riel', 'name_km' => 'រៀល', 'symbol' => '៛'],
            ['id' => Currency::USD_ID, 'code' => 'USD', 'name_en' => 'US Dollar', 'name_km' => 'ដុល្លារអាមេរិក', 'symbol' => '$'],
        ], ['id'], ['name_en', 'name_km', 'code', 'symbol']);

        VendorInventoryStatus::upsert([
            ['id' => VendorInventoryStatus::AVAILABLE_ID, 'name_en' => 'Available', 'name_km' => 'មានលក់'],
            ['id' => VendorInventoryStatus::OUT_OF_STOCK_ID, 'name_en' => 'Out of Stock', 'name_km' => 'អស់ពីស្តុក'],
            ['id' => VendorInventoryStatus::HIDDEN_ID, 'name_en' => 'Hidden', 'name_km' => 'លាក់'],
            ['id' => VendorInventoryStatus::PENDING_REVIEW_ID, 'name_en' => 'Pending Review', 'name_km' => 'រង់ចាំការពិនិត្យ'],
        ], ['id'], ['name_en', 'name_km']);

        OrderStatus::upsert([
            ['id' => OrderStatus::PENDING_ID, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
            ['id' => OrderStatus::CONFIRMED_ID, 'name_en' => 'Confirmed', 'name_km' => 'បញ្ជាក់'],
            ['id' => OrderStatus::PREPARING_ID, 'name_en' => 'Preparing', 'name_km' => 'កំពុងរៀបចំ'],
            ['id' => OrderStatus::OUT_FOR_DELIVERY_ID, 'name_en' => 'Out for Delivery', 'name_km' => 'កំពុងដឹកជញ្ជូន'],
            ['id' => OrderStatus::DELIVERED_ID, 'name_en' => 'Delivered', 'name_km' => 'បានទទួល'],
            ['id' => OrderStatus::CANCELLED_ID, 'name_en' => 'Cancelled', 'name_km' => 'លុបចោល'],
            ['id' => OrderStatus::AWAITING_PAYMENT_ID, 'name_en' => 'Awaiting Payment', 'name_km' => 'រង់ចាំការទូទាត់ប្រាក់'],
        ], ['id'], ['name_en', 'name_km']);

        PaymentMethodType::upsert([
            ['id' => PaymentMethodType::WALLET_ID, 'name_en' => 'Wallet', 'name_km' => 'កាបូបលុយ'],
            ['id' => PaymentMethodType::COD_ID, 'name_en' => 'Cash On Delivery', 'name_km' => 'សាច់ប្រាក់ពេលទទួលទំនិញ'],
        ], ['id'], ['name_en', 'name_km']);

        PaymentMethodStatus::upsert([
            ['id' => PaymentMethodStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
        ], ['id'], ['name_en', 'name_km']);

        ProductCategoryStatus::upsert([
            ['id' => ProductCategoryStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => ProductCategoryStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
        ], ['id'], ['name_en', 'name_km']);

        ProductStatus::upsert([
            ['id' => ProductStatus::DRAFT_ID, 'name_en' => 'Draft', 'name_km' => 'ព្រាង'],
            ['id' => ProductStatus::PUBLISHED_ID, 'name_en' => 'Published', 'name_km' => 'បានផ្សព្វផ្សាយ'],
            ['id' => ProductStatus::ARCHIVED_ID, 'name_en' => 'Archived', 'name_km' => 'បានរក្សាទុកក្នុងប័ណ្ណសារ'],
        ], ['id'], ['name_en', 'name_km']);

        ProductType::upsert([
            ['id' => ProductType::DEFAULT_ID, 'name_en' => 'Fresh Produce', 'name_km' => 'ផលិតផលស្រស់'],
        ], ['id'], ['name_en', 'name_km']);

        Unit::upsert([
            ['id' => 1, 'name_en' => 'Kilogram', 'name_km' => 'គីឡូក្រាម', 'symbol' => 'kg', 'conversion_to_base' => 1.0],
            ['id' => 2, 'name_en' => 'Gram', 'name_km' => 'ក្រាម', 'symbol' => 'g', 'conversion_to_base' => 0.001],
            ['id' => 3, 'name_en' => 'Piece', 'name_km' => 'ដុំ', 'symbol' => 'pcs', 'conversion_to_base' => 1.0],
            ['id' => 4, 'name_en' => 'Bundle', 'name_km' => 'បាច់', 'symbol' => 'bundle', 'conversion_to_base' => 1.0],
        ], ['id'], ['name_en', 'name_km', 'symbol', 'conversion_to_base']);

        ExchangeRate::create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4000.00000000',
        ]);
    }

    public function test_add_item_to_cart_returns_cart_with_item(): void
    {
        [$vendor, $inventory] = $this->createVendorWithInventory();
        $user = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/cart', [
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '2',
        ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => ['carts', 'total'],
            ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '2.00',
            'cart_status_id' => CartStatus::ACTIVE_ID,
        ]);
    }

    public function test_update_cart_item_quantity(): void
    {
        [$vendor, $inventory] = $this->createVendorWithInventory();
        $user = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $addResponse = $this->actingAs($user)->postJson('/api/v1/cart', [
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '1',
        ]);
        $addResponse->assertOk();
        $cartId = $addResponse->json('data.carts.0.id');

        $updateResponse = $this->actingAs($user)->putJson("/api/v1/cart/{$cartId}", [
            'quantity' => '3',
        ]);

        $updateResponse->assertOk();

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'quantity' => '3.00',
            'cart_status_id' => CartStatus::ACTIVE_ID,
        ]);
    }

    public function test_remove_item_from_cart(): void
    {
        [$vendor, $inventory] = $this->createVendorWithInventory();
        $user = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $addResponse = $this->actingAs($user)->postJson('/api/v1/cart', [
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '1',
        ]);
        $addResponse->assertOk();
        $cartId = $addResponse->json('data.carts.0.id');

        $deleteResponse = $this->actingAs($user)->deleteJson("/api/v1/cart/{$cartId}");

        $deleteResponse->assertOk();

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'cart_status_id' => CartStatus::REMOVED_ID,
        ]);
    }

    public function test_checkout_creates_orders_and_clears_cart(): void
    {
        [$vendor, $inventory] = $this->createVendorWithInventory();
        $user = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson('/api/v1/cart', [
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '2',
        ])->assertOk();

        $user->paymentMethods()->create([
            'payment_method_type_id' => PaymentMethodType::COD_ID,
            'payment_method_status_id' => PaymentMethodStatus::ACTIVE_ID,
        ]);

        $checkoutResponse = $this->actingAs($user)->postJson('/api/v1/cart/checkout', [
            'address_id' => $address->id,
            'payment_method_type_id' => PaymentMethodType::COD_ID,
            'order_type_id' => 1,
        ]);

        $checkoutResponse->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => ['data'],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'order_status_id' => OrderStatus::PENDING_ID,
        ]);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
            'cart_status_id' => CartStatus::ACTIVE_ID,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'cart_status_id' => CartStatus::CHECKED_OUT_ID,
        ]);
    }

    public function test_checkout_with_empty_cart_fails(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/cart/checkout', [
            'address_id' => 1,
            'payment_method_type_id' => PaymentMethodType::WALLET_ID,
            'order_type_id' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_cart_is_scoped_per_user(): void
    {
        [$vendor, $inventory] = $this->createVendorWithInventory();
        $user1 = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $user2 = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $this->actingAs($user1)->postJson('/api/v1/cart', [
            'vendor_inventory_id' => $inventory->id,
            'quantity' => '1',
        ])->assertOk();

        $user2Response = $this->actingAs($user2)->getJson('/api/v1/cart');

        $user2Response->assertOk();
        $this->assertEmpty($user2Response->json('data.carts'));
    }

    private function createVendorWithInventory(): array
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        VendorProfile::create([
            'user_id' => $vendor->id,
            'business_name' => 'Test Vendor',
            'contact_phone' => '0123456789',
        ]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'default_unit_id' => 1,
        ]);
        $inventory = VendorInventory::query()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'currency_id' => Currency::USD_ID,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'price' => '5.00',
            'stock_quantity' => '100.00',
            'unit_id' => 1,
        ]);

        return [$vendor, $inventory];
    }
}
