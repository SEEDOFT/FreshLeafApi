<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ProductStatus::upsert([
            ['id' => ProductStatus::ACTIVE, 'code' => 'ACTIVE', 'name' => 'Active'],
        ], ['id'], ['code', 'name']);

        ProductType::upsert([
            ['id' => 1, 'code' => 'ORGANIC', 'name' => 'Organic'],
        ], ['id'], ['code', 'name']);

        ProductCategory::upsert([
            ['id' => 1, 'slug' => 'veg', 'name_en' => 'Veg', 'name_km' => 'Veg (KM)'],
        ], ['id'], ['slug', 'name_en', 'name_km']);

        Unit::upsert([
            ['id' => 1, 'symbol' => 'KG', 'name' => 'KG'],
        ], ['id'], ['symbol', 'name']);
    }

    /**
     * Test product variant creation.
     */
    public function test_product_variant_can_be_created(): void
    {
        $product = Product::factory()->create([
            'product_category_id' => 1,
            'product_type_id' => 1,
            'default_unit_id' => 1,
            'product_status_id' => ProductStatus::ACTIVE,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'unit_id' => 1,
            'name' => '500g Pack',
            'price' => 15.50,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => '500g Pack',
            'price' => 15.50,
        ]);

        $this->assertEquals($product->id, $variant->product->id);
        $this->assertEquals(1, $variant->unit->id);
    }
}
