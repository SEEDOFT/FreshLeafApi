<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * @mixin ProductCategory
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_km' => $this->name_km,
            'localized_name' => $this->localizedName,
            'slug' => $this->slug,
            'description_en' => $this->description_en,
            'description_km' => $this->description_km,
            'localized_description' => $this->localizedDescription,
            'selling_unit' => $this->selling_unit,
            'price_per_unit' => $this->price_per_unit,
            'available_stock' => $this->available_stock,
            'farm_name_location' => $this->farm_name_location,
            'farming_method' => $this->farming_method,
            'harvest_date' => $this->harvest_date ? $this->harvest_date->toDateString() : null,
            'is_active' => $this->is_active,
            'is_organic' => $this->is_organic,
            'shelf_life_days' => $this->shelf_life_days,
            'nutrition_data' => $this->nutrition_data,
            'product_category_id' => $this->product_category_id,
            'organic_category_id' => $this->organic_category_id,
            'product_type_id' => $this->product_type_id,
            'default_unit_id' => $this->default_unit_id,
            'product_status_id' => $this->product_status_id,
            'product_category' => $this->whenLoaded('productCategory', fn () => [
                'id' => $this->productCategory->id,
                'name_en' => $this->productCategory->name_en,
                'name_km' => $this->productCategory->name_km,
                'localized_name' => $this->productCategory->localizedName,
                'slug' => $this->productCategory->slug,
            ]),
            'organic_category' => $this->whenLoaded('organicCategory', fn () => [
                'id' => $this->organicCategory->id,
                'name_en' => $this->organicCategory->name_en,
                'name_km' => $this->organicCategory->name_km,
                'localized_name' => $this->organicCategory->localizedName,
                'slug' => $this->organicCategory->slug,
            ]),
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ]),
            'default_unit' => $this->whenLoaded('defaultUnit', fn () => [
                'id' => $this->defaultUnit->id,
                'name' => $this->defaultUnit->name,
                'symbol' => $this->defaultUnit->symbol,
            ]),
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ]),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(static fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'unit_id' => $variant->unit_id,
                'quantity_in_unit' => $variant->quantity_in_unit,
                'price' => $variant->price,
            ])->values()->all()),
            'created_at' => \optional($this->created_at)->toIso8601String(),
            'updated_at' => \optional($this->updated_at)->toIso8601String(),
        ];
    }
}
