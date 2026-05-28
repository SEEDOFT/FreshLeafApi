<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Wallets\Pages;

use App\Filament\Vendor\Resources\Wallets\WalletResource;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListWallets extends ListRecords
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
