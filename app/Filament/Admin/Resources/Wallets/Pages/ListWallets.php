<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
