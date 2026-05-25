<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin ProductCategory
 */
class ProductCategoryResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Language', 'km');

        return [
            'id' => $this->id,
            'name' => $locale == 'km' ? $this->name_km : $this->name_en,
            'description' => $locale == 'km' ? $this->description_km : $this->description_en,
            'image_url' => $this->imageUrl,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
