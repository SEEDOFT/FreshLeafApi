<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Pages;

use App\Filament\Admin\Resources\WalletTransactions\WalletTransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateWalletTransaction extends CreateRecord
{
    #[Override]
    protected static string $resource = WalletTransactionResource::class;
}
