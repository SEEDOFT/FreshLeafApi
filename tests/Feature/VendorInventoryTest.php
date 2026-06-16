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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorInventoryTest extends TestCase
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

        ProductCategoryStatus::upsert([
            ['id' => ProductCategoryStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => ProductCategoryStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
        ], ['id'], ['name_en', 'name_km']);

        ProductStatus::upsert([
            ['id' => ProductStatus::DRAFT_ID, 'name_en' => 'Draft', 'name_km' => 'ព្រាង'],
            ['id' => ProductStatus::PUBLISHED_ID, 'name_en' => 'Published', 'name_km' => 'បានផ្សព្វផ្សាយ'],
            ['id' => ProductStatus::ARCHIVED_ID, 'name_en' => 'Archived', 'name_km' => 'បានរក្សាទុកក្នុងប័ណ្ណសារ'],
        ], ['id'], ['name_en', 'name_km']);

        VendorInventoryStatus::upsert([
            ['id' => VendorInventoryStatus::AVAILABLE_ID, 'name_en' => 'Available', 'name_km' => 'មានលក់'],
            ['id' => VendorInventoryStatus::OUT_OF_STOCK_ID, 'name_en' => 'Out of Stock', 'name_km' => 'អស់ពីស្តុក'],
            ['id' => VendorInventoryStatus::HIDDEN_ID, 'name_en' => 'Hidden', 'name_km' => 'លាក់'],
            ['id' => VendorInventoryStatus::PENDING_REVIEW_ID, 'name_en' => 'Pending Review', 'name_km' => 'រង់ចាំការពិនិត្យ'],
        ], ['id'], ['name_en', 'name_km']);

        Unit::upsert([
            ['id' => 1, 'name_en' => 'Kilogram', 'name_km' => 'គីឡូក្រាម', 'symbol' => 'kg', 'conversion_to_base' => 1.0],
            ['id' => 2, 'name_en' => 'Gram', 'name_km' => 'ក្រាម', 'symbol' => 'g', 'conversion_to_base' => 0.001],
            ['id' => 3, 'name_en' => 'Piece', 'name_km' => 'ដុំ', 'symbol' => 'pcs', 'conversion_to_base' => 1.0],
            ['id' => 4, 'name_en' => 'Bundle', 'name_km' => 'បាច់', 'symbol' => 'bundle', 'conversion_to_base' => 1.0],
        ], ['id'], ['name_en', 'name_km', 'symbol', 'conversion_to_base']);

        ProductType::upsert([
            ['id' => ProductType::DEFAULT_ID, 'name_en' => 'Fresh Produce', 'name_km' => 'ផលិតផលស្រស់'],
        ], ['id'], ['name_en', 'name_km']);

        Currency::upsert([
            ['id' => Currency::KHR_ID, 'code' => 'KHR', 'name_en' => 'Cambodian Riel', 'name_km' => 'រៀល', 'symbol' => '៛'],
            ['id' => Currency::USD_ID, 'code' => 'USD', 'name_en' => 'US Dollar', 'name_km' => 'ដុល្លារអាមេរិក', 'symbol' => '$'],
        ], ['id'], ['name_en', 'name_km', 'code', 'symbol']);

        ExchangeRate::create([
            'from_currency_id' => Currency::USD_ID,
            'to_currency_id' => Currency::KHR_ID,
            'rate' => '4000.00000000',
        ]);
    }

    private function createAuthenticatedUser(): User
    {
        return User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
    }

    private function createActiveInventory(): VendorInventory
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

        return VendorInventory::factory()->create([
            'vendor_id' => $vendor->id,
            'stock_quantity' => '50.00',
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);
    }

    public function test_list_products_returns_only_active_inventories(): void
    {
        $activeInventory = $this->createActiveInventory();

        $hiddenVendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        VendorProfile::create([
            'user_id' => $hiddenVendor->id,
            'business_name' => 'Hidden Vendor',
            'contact_phone' => '0123456789',
        ]);
        VendorInventory::factory()->create([
            'vendor_id' => $hiddenVendor->id,
            'stock_quantity' => '10.00',
            'inventory_status_id' => VendorInventoryStatus::HIDDEN_ID,
            'unit_id' => 1,
        ]);

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $productIds = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertContains($activeInventory->id, $productIds);
    }

    public function test_list_products_excludes_inactive_vendor_inventories(): void
    {
        $activeInventory = $this->createActiveInventory();

        $inactiveVendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::INACTIVE_ID,
        ]);
        VendorProfile::create([
            'user_id' => $inactiveVendor->id,
            'business_name' => 'Inactive Vendor',
            'contact_phone' => '0123456789',
        ]);
        $inactiveInventory = VendorInventory::factory()->create([
            'vendor_id' => $inactiveVendor->id,
            'stock_quantity' => '10.00',
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson('/api/v1/products');

        $response->assertOk();
        $productIds = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertContains($activeInventory->id, $productIds);
        $this->assertNotContains($inactiveInventory->id, $productIds);
    }

    public function test_product_detail_returns_full_info(): void
    {
        $inventory = $this->createActiveInventory();

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson("/api/v1/products/{$inventory->id}");

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'id',
                    'price',
                    'stock_quantity',
                    'product',
                    'vendor',
                ],
            ]);
    }

    public function test_product_detail_returns_404_for_inactive_inventory(): void
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
        $inventory = VendorInventory::factory()->create([
            'vendor_id' => $vendor->id,
            'inventory_status_id' => VendorInventoryStatus::HIDDEN_ID,
            'unit_id' => 1,
        ]);

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson("/api/v1/products/{$inventory->id}");

        $response->assertNotFound();
    }

    public function test_list_products_filters_by_category(): void
    {
        $category1 = ProductCategory::factory()->create([
            'product_category_status_id' => ProductCategoryStatus::ACTIVE_ID,
        ]);
        $category2 = ProductCategory::factory()->create([
            'product_category_status_id' => ProductCategoryStatus::ACTIVE_ID,
        ]);

        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        VendorProfile::create([
            'user_id' => $vendor->id,
            'business_name' => 'Test Vendor',
            'contact_phone' => '0123456789',
        ]);

        $product1 = Product::factory()->create([
            'product_category_id' => $category1->id,
            'product_status_id' => ProductStatus::PUBLISHED_ID,
            'default_unit_id' => 1,
        ]);
        VendorInventory::factory()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product1->id,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);

        $product2 = Product::factory()->create([
            'product_category_id' => $category2->id,
            'product_status_id' => ProductStatus::PUBLISHED_ID,
            'default_unit_id' => 1,
        ]);
        $inv2 = VendorInventory::factory()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product2->id,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson('/api/v1/products?category_id='.$category2->id);

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertContains($inv2->id, $ids);
    }

    public function test_list_products_filters_by_search(): void
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

        $product = Product::factory()->create([
            'name_en' => 'Organic Fresh Lettuce',
            'product_status_id' => ProductStatus::PUBLISHED_ID,
            'default_unit_id' => 1,
        ]);
        VendorInventory::factory()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);

        $productOther = Product::factory()->create([
            'name_en' => 'Carrot',
            'product_status_id' => ProductStatus::PUBLISHED_ID,
            'default_unit_id' => 1,
        ]);
        $invOther = VendorInventory::factory()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $productOther->id,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson('/api/v1/products?search=Carrot');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertContains($invOther->id, $ids);
    }

    public function test_vendor_profile_returns_paginated_products(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Fresh Farm',
            'contact_phone' => '+855123456789',
            'province' => 'Phnom Penh',
            'is_verified' => true,
        ]);

        VendorInventory::factory()->count(3)->create([
            'vendor_id' => $vendor->id,
            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
            'unit_id' => 1,
        ]);

        $user = $this->createAuthenticatedUser();
        $response = $this->actingAs($user)->getJson("/api/v1/vendors/{$vendor->id}");

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status' => ['code', 'success', 'message'],
                'data' => [
                    'vendor',
                    'products' => ['data', 'meta'],
                ],
            ]);
    }
}
