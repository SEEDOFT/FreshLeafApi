<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductStatus;
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

        ProductStatus::factory()->create(['id' => ProductStatus::ACTIVE, 'code' => 'active']);
    }

    /**
     * Test product variant creation.
     */
    public function test_product_variant_can_be_created(): void
    {
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'name' => '500g Pack',
            'price' => 15.50,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => '500g Pack',
            'price' => 15.50,
        ]);

        $this->assertEquals($product->id, $variant->product->id);
        $this->assertEquals($unit->id, $variant->unit->id);
    }
}
