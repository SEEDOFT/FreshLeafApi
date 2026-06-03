<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Models\PaymentMethodType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Override;

/**
 * @mixin PaymentMethodType
 */
class PaymentMethodTypeResource extends JsonResource
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
            'code' => PaymentMethodType::codeById($this->id),
            'name' => $locale === 'km' ? $this->name_km : $this->name_en,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
