<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::insert([
            ['id' => UserStatus::ACTIVE, 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'name' => 'Deleted'],
        ]);

        UserType::insert([
            ['id' => UserType::CONSUMER, 'name' => 'Consumer'],
            ['id' => UserType::OPERATION, 'name' => 'Operation'],
            ['id' => UserType::ADMIN, 'name' => 'Admin'],
        ]);

        ProductStatus::factory()->create(['id' => ProductStatus::ACTIVE, 'code' => 'active', 'name' => 'Active']);
        ProductStatus::factory()->create(['id' => ProductStatus::INACTIVE, 'code' => 'inactive', 'name' => 'Inactive']);
        ProductStatus::factory()->create(['id' => ProductStatus::DRAFT, 'code' => 'draft', 'name' => 'Draft']);
    }

    public function test_admin_can_crud_products(): void
    {
        $admin = User::factory()->create([
            'user_status_id' => UserStatus::ACTIVE,
            'user_type_id' => UserType::ADMIN,
        ]);

        $token = $admin->createToken('admin-product')->plainTextToken;

        $category = ProductCategory::factory()->create(['name' => 'Testing Category', 'slug' => 'testing-category']);
        $productType = ProductType::factory()->create(['code' => 'fresh', 'name' => 'Fresh']);
        $unit = Unit::factory()->create(['name' => 'Kilogram', 'symbol' => 'kg']);

        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/admin/products', [
                'product_category_id' => $category->id,
                'product_type_id' => $productType->id,
                'default_unit_id' => $unit->id,
                'product_status_id' => ProductStatus::ACTIVE,
                'name' => 'Test CRUD Product',
                'slug' => 'test-crud-product',
                'description' => 'Created by product CRUD test',
                'nutrition_data' => ['energy' => '100 kcal'],
                'shelf_life_days' => 7,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.slug', 'test-crud-product');

        $productId = $createResponse->json('data.id');

        $this->withToken($token)
            ->getJson('/api/v1/admin/products')
            ->assertOk()
            ->assertJsonPath('status.success', true);

        $this->withToken($token)
            ->getJson('/api/v1/admin/products/'.$productId)
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.id', $productId);

        $this->withToken($token)
            ->putJson('/api/v1/admin/products/'.$productId, [
                'name' => 'Updated CRUD Product',
                'description' => 'Updated by product CRUD test',
                'shelf_life_days' => 10,
            ])
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.name', 'Updated CRUD Product');

        $this->withToken($token)
            ->deleteJson('/api/v1/admin/products/'.$productId)
            ->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertSoftDeleted(Product::class, ['id' => $productId]);
    }
}
