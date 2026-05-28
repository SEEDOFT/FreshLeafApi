<?php

declare(strict_types=1);

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Override;

/**
 * @property-read int $id
 * @property-read string|null $name_en
 * @property-read string|null $name_km
 * @property-read string|null $code
 * @property-read string|null $color
 */
class StatusResource extends JsonResource
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

        $data = [
            'id' => $this->id,
            'name' => $locale === 'km' ? $this->name_km : $this->name_en,
        ];

        if (isset($this->code)) {
            $data['code'] = $this->code;
        }

        if (isset($this->color)) {
            $data['color'] = $this->color;
        }

        return $data;
    }
}
