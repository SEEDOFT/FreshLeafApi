<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateWallet extends CreateRecord
{
    #[Override]
    protected static string $resource = WalletResource::class;
}
