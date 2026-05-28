<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Wallets\Pages;

use App\Filament\Vendor\Resources\Wallets\RelationManagers\TransactionsRelationManager;
use App\Filament\Vendor\Resources\Wallets\WalletResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewWallet extends ViewRecord
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected function getAllRelationManagers(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
