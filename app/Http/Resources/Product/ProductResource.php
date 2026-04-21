<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'shelf_life_days' => $this->shelf_life_days,
            'nutrition_data' => $this->nutrition_data,
            'product_category_id' => $this->product_category_id,
            'product_type_id' => $this->product_type_id,
            'default_unit_id' => $this->default_unit_id,
            'product_status_id' => $this->product_status_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
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
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
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
