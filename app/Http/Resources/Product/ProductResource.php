<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * @mixin ProductCategory
 * @mixin ProductStatus
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
            'image_url' => $this->image_url,
            'nutrition_data' => $this->nutrition_data,
            'product_category_id' => $this->product_category_id,
            'product_type_id' => $this->product_type_id,
            'default_unit_id' => $this->default_unit_id,
            'product_status_id' => $this->product_status_id,
            'product_category' => $this->whenLoaded('productCategory', fn () => $this->productCategory
            ? [
                'id' => $this->productCategory->id,
                'name_en' => $this->productCategory->name_en,
                'name_km' => $this->productCategory->name_km,
                'localized_name' => $this->productCategory->localizedName,
                'slug' => $this->productCategory->slug,
            ] : null),
            'type' => $this->whenLoaded('type', fn () => $this->type ? [
                'name' => translate($this->type->name_en, $this->type->name_km),
            ] : null),
            'default_unit' => $this->whenLoaded('defaultUnit', fn () => [
                'id' => $this->default_unit_id,
                'name' => translate($this->defaultUnit->name_en, $this->defaultUnit->name_km),
                'symbol' => $this->defaultUnit->symbol,
            ]),
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->product_status_id,
                'name' => translate($this->status->name_en, $this->status->name_km),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
