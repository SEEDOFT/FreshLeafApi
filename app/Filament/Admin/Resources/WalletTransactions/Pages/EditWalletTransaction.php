<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Pages;

use App\Filament\Admin\Resources\WalletTransactions\WalletTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditWalletTransaction extends EditRecord
{
    #[Override]
    protected static string $resource = WalletTransactionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
