<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Shared\StatusResource;
use App\Http\Resources\Shared\TypeResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * @mixin Product
 * @mixin ProductCategory
 * @mixin ProductStatus
 */
class ProductResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Language', App::getLocale());

        return [
            'id' => $this->id,
            'name' => $locale === 'km' ? $this->name_km : $this->name_en,
            'slug' => $this->slug,
            'description' => $locale === 'km'
                ? $this->description_km
                : $this->description_en,
            'image_url' => $this->image_url ? Storage::disk('public')->url($this->image_url) : null,
            'nutrition_data' => $this->nutrition_data,
            'product_category' => $this->whenLoaded(
                'productCategory',
                fn () => $this->productCategory
                    ? [
                        'id' => $this->productCategory->id,
                        'name' => $locale === 'km'
                            ? $this->productCategory->name_km
                            : $this->productCategory->name_en,
                    ]
                    : null
            ),
            'type' => new TypeResource($this->whenLoaded('type')),
            'status' => new StatusResource($this->whenLoaded('status')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
