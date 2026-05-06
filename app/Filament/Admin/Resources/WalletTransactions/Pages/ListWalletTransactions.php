<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Pages;

use App\Filament\Admin\Resources\WalletTransactions\WalletTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListWalletTransactions extends ListRecords
{
    #[Override]
    protected static string $resource = WalletTransactionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
