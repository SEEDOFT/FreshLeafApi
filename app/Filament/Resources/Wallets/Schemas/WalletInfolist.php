<?php

declare(strict_types=1);

namespace App\Filament\Resources\Wallets\Schemas;

use App\Models\Currency;
use App\Models\Wallet;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_name')
                    ->label(__('admin.resources.user.full_name'))
                    ->getStateUsing(static fn (Wallet $record) => $record->user ? "{$record->user->last_name} {$record->user->first_name}" : '-'),
                TextEntry::make('user.email')
                    ->label(__('admin.resources.user.email')),
                TextEntry::make('currency.code')
                    ->label(__('admin.resources.wallet.currency')),
                TextEntry::make('balance')
                    ->label(__('admin.resources.wallet.balance'))
                    ->getStateUsing(static function (Wallet $record): string {
                        $id = $record->currency->id ?? null;
                        $symbol = $record->currency->symbol ?? '';
                        $balance = number_format((float) $record->balance, 2);

                        return $id === Currency::USD_ID
                            ? "{$symbol} {$balance}"
                            : "{$balance} {$symbol}";
                    }),
                TextEntry::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime(),
            ]);
    }
}
