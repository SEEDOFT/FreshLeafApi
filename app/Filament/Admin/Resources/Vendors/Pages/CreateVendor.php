<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Pages;

use App\Filament\Admin\Resources\Vendors\VendorResource;
use App\Models\UserType;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_type_id'] = UserType::VENDOR;

        return $data;
    }
}
