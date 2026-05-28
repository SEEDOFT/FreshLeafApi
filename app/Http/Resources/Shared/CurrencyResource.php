<?php

declare(strict_types=1);

namespace App\Http\Resources\Shared;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Override;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
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
            'code' => $this->code,
            'symbol' => $this->symbol,
            'name' => $locale === 'km' ? $this->name_km : $this->name_en,
        ];
    }
}
