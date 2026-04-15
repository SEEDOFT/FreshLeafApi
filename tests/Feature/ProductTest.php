<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ProductStatus::factory()->create(['id' => ProductStatus::ACTIVE, 'code' => 'active']);
        ProductStatus::factory()->create(['id' => ProductStatus::INACTIVE, 'code' => 'inactive']);
        ProductStatus::factory()->create(['id' => ProductStatus::DRAFT, 'code' => 'draft']);
    }

    /**
     * Test product creation and attributes.
     */
    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Fresh Leaf Tea',
            'slug' => 'fresh-leaf-tea',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Fresh Leaf Tea',
            'slug' => 'fresh-leaf-tea',
        ]);

        $this->assertIsArray($product->nutrition_data);
        $this->assertEquals('fresh-leaf-tea', $product->slug);
    }

    /**
     * Test slug generation.
     */
    public function test_product_generates_slug_on_creation(): void
    {
        $product = Product::factory()->create([
            'name' => 'Organic Green Tea',
            'slug' => null,
        ]);

        $this->assertEquals('organic-green-tea', $product->slug);
    }

    /**
     * Test active scope.
     */
    public function test_product_active_scope(): void
    {
        Product::factory()->count(3)->create(['product_status_id' => ProductStatus::ACTIVE]);
        Product::factory()->count(2)->create(['product_status_id' => ProductStatus::INACTIVE]);

        $activeProducts = Product::active()->get();

        $this->assertCount(3, $activeProducts);
    }

    /**
     * Test byCategory scope.
     */
    public function test_product_by_category_scope(): void
    {
        $category1 = ProductCategory::factory()->create();
        $category2 = ProductCategory::factory()->create();

        Product::factory()->count(2)->create(['product_category_id' => $category1->id]);
        Product::factory()->count(3)->create(['product_category_id' => $category2->id]);

        $this->assertCount(2, Product::byCategory($category1)->get());
        $this->assertCount(3, Product::byCategory($category2->id)->get());
    }

    /**
     * Test soft deletes.
     */
    public function test_product_soft_deletes(): void
    {
        $product = Product::factory()->create();

        $product->delete();

        $this->assertSoftDeleted($product);
        $this->assertCount(0, Product::all());
        $this->assertCount(1, Product::withTrashed()->get());
    }
}
