<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewWallet extends ViewRecord
{
    protected static string $resource = WalletResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
