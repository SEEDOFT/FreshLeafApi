<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\ExchangeRate;
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
use App\Models\WishlistStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistTest extends TestCase
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

        WishlistStatus::upsert([
            ['id' => WishlistStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => WishlistStatus::DELETED_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
        ], ['id'], ['name_en', 'name_km']);

        ProductCategoryStatus::upsert([
            ['id' => ProductCategoryStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
        ], ['id'], ['name_en', 'name_km']);

        ProductStatus::upsert([
            ['id' => ProductStatus::DRAFT_ID, 'name_en' => 'Draft', 'name_km' => 'សេចក្តីព្រាង'],
            ['id' => ProductStatus::PUBLISHED_ID, 'name_en' => 'Published', 'name_km' => 'បោះពុម្ព'],
            ['id' => ProductStatus::ARCHIVED_ID, 'name_en' => 'Archived', 'name_km' => 'ទុកក្នុងប័ណ្ណសារ'],
        ], ['id'], ['name_en', 'name_km']);

        ProductType::upsert([
            ['id' => ProductType::DEFAULT_ID, 'name_en' => 'Default', 'name_km' => 'លំនាំដើម'],
        ], ['id'], ['name_en', 'name_km']);

        Unit::upsert([
            ['id' => 1, 'name_en' => 'Kilogram', 'name_km' => 'គីឡូក្រាម', 'symbol' => 'kg', 'conversion_to_base' => 1.0],
            ['id' => 2, 'name_en' => 'Piece', 'name_km' => 'ដុំ', 'symbol' => 'pc', 'conversion_to_base' => 1.0],
        ], ['id'], ['name_en', 'name_km', 'symbol', 'conversion_to_base']);

        VendorInventoryStatus::upsert([
            ['id' => VendorInventoryStatus::AVAILABLE_ID, 'name_en' => 'Available', 'name_km' => 'មាន'],
            ['id' => VendorInventoryStatus::OUT_OF_STOCK_ID, 'name_en' => 'Out of Stock', 'name_km' => 'អស់ស្តុក'],
        ], ['id'], ['name_en', 'name_km']);

        Currency::upsert([
            ['id' => Currency::KHR_ID, 'code' => Currency::KHR, 'name_en' => 'Cambodian Riel', 'name_km' => 'រៀល', 'symbol' => '៛'],
            ['id' => Currency::USD_ID, 'code' => Currency::USD, 'name_en' => 'US Dollar', 'name_km' => 'ដុល្លារ', 'symbol' => '$'],
        ], ['id'], ['code', 'name_en', 'name_km', 'symbol']);

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

    private static int $productCounter = 0;

    private function createVendorInventory(): VendorInventory
    {
        $vendor = User::factory()->create(['user_type_id' => UserType::VENDOR_ID]);
        VendorProfile::create([
            'user_id' => $vendor->id,
            'business_name' => 'FreshLeaf Farm',
            'contact_phone' => '0123456789',
            'province' => 'Phnom Penh',
            'is_verified' => true,
        ]);

        $category = ProductCategory::find(ProductCategory::LEAFY);
        if (! $category) {
            $category = ProductCategory::factory()->create([
                'id' => ProductCategory::LEAFY,
                'name_en' => 'Vegetables',
                'name_km' => 'បន្លែ',
                'slug' => 'vegetables',
                'product_category_status_id' => ProductCategoryStatus::ACTIVE_ID,
            ]);
        }

        self::$productCounter++;
        $slug = 'fresh-lettuce-'.self::$productCounter;

        $product = Product::create([
            'product_category_id' => $category->id,
            'product_type_id' => ProductType::DEFAULT_ID,
            'default_unit_id' => 1,
            'product_status_id' => ProductStatus::PUBLISHED_ID,
            'name_en' => 'Fresh Lettuce '.self::$productCounter,
            'name_km' => 'សាលាដ '.self::$productCounter,
            'slug' => $slug,
        ]);

        return VendorInventory::create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'currency_id' => Currency::USD_ID,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'price' => '2.50',
            'stock_quantity' => '100',
            'unit_id' => 1,
        ]);
    }

    public function test_list_requires_authentication(): void
    {
        $this->getJson('/api/v1/wishlist')->assertUnauthorized();
    }

    public function test_toggle_requires_authentication(): void
    {
        $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => 1,
        ])->assertUnauthorized();
    }

    public function test_empty_wishlist_returns_empty(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/wishlist');

        $response->assertOk()
            ->assertJsonPath('status.success', true);
    }

    public function test_user_can_add_item_to_wishlist_via_toggle(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $inventory = $this->createVendorInventory();

        $response = $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'vendor_inventory_id' => $inventory->id,
            'wishlist_status_id' => WishlistStatus::ACTIVE_ID,
        ]);
    }

    public function test_user_can_remove_item_from_wishlist_via_toggle(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $inventory = $this->createVendorInventory();

        $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory->id,
        ]);

        $response = $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'vendor_inventory_id' => $inventory->id,
            'wishlist_status_id' => WishlistStatus::DELETED_ID,
        ]);
    }

    public function test_list_returns_only_active_items(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $inventory1 = $this->createVendorInventory();
        $inventory2 = $this->createVendorInventory();
        $inventory3 = $this->createVendorInventory();

        $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory1->id,
        ]);

        $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory2->id,
        ]);

        $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory3->id,
        ]);

        $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => $inventory2->id,
        ]);

        $response = $this->getJson('/api/v1/wishlist');

        $response->assertOk()
            ->assertJsonPath('status.success', true);
    }

    public function test_toggle_requires_valid_vendor_inventory(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wishlist/toggle', [
            'vendor_inventory_id' => 99999,
        ]);

        $response->assertStatus(422);
    }
}
