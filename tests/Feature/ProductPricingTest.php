<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDiscount;
use App\Models\ProductStatus;
use App\Models\ProductType;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_variant_calculates_active_price_with_discount(): void
    {
        // 1. Setup Data
        $category = ProductCategory::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
        $type = ProductType::create(['name' => 'Organic', 'code' => 'organic']);
        $unit = Unit::create(['name' => 'Kilogram', 'symbol' => 'KG']);
        $status = ProductStatus::create(['name' => 'Active', 'code' => 'active']);

        $product = Product::create([
            'product_category_id' => $category->id,
            'product_type_id' => $type->id,
            'default_unit_id' => $unit->id,
            'product_status_id' => $status->id,
            'name' => 'Organic Tomato',
            'slug' => 'organic-tomato',
            'is_organic' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'name' => '1 KG',
            'quantity_in_unit' => 1,
            'price' => 10.00,
        ]);

        // 2. Setup Currency & Rate
        User::factory()->create(['id' => 1]);
        $usd = Currency::updateOrCreate(['code' => 'USD'], ['name' => 'USD', 'symbol' => '$']);
        $khr = Currency::updateOrCreate(['code' => 'KHR'], ['name' => 'KHR', 'symbol' => '៛']);

        ExchangeRate::create([
            'from_currency_id' => $usd->id,
            'to_currency_id' => $khr->id,
            'rate' => 4100.00,
        ]);

        // 3. Test Base Price (No Discount)
        $this->assertEquals(10.00, $variant->activePrice);
        $this->assertEquals(41000.00, $variant->activePriceKhr);

        // 4. Add Discount (10%)
        ProductDiscount::create([
            'product_id' => $product->id,
            'discount_percentage' => 10,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        // Clear relationship cache
        $product->unsetRelation('activeDiscount');
        $variant->unsetRelation('product');

        // 5. Test Discounted Price
        // 10.00 - 10% = 9.00
        $this->assertEquals(9.00, $variant->activePrice);

        // 9.00 * 4100 = 36900
        $this->assertEquals(36900.00, $variant->activePriceKhr);
    }
}
