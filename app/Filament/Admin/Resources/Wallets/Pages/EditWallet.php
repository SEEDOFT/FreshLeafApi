<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditWallet extends EditRecord
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
