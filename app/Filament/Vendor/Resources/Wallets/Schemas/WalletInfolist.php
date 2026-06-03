<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Wallets\Schemas;

use App\Models\Currency;
use App\Models\Wallet;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->components([
                Section::make(__('admin.resources.wallet.label'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.user.first_name'))
                            ->getStateUsing(fn (Wallet $record) => $record->vendor?->first_name),
                        TextEntry::make('last_name')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.user.last_name'))
                            ->getStateUsing(fn (Wallet $record) => $record->vendor?->last_name),
                        TextEntry::make('currency.code')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.wallet.currency'))
                            ->placeholder('-'),
                        TextEntry::make('balance')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.wallet.balance'))
                            ->getStateUsing(static function (Wallet $record): string {
                                $id = $record->currency->id ?? null;
                                $symbol = $record->currency->symbol ?? '';
                                $balance = number_format((float) $record->balance, 2);

                                return $id === Currency::USD_ID
                                ? "{$symbol} {$balance}"
                                    : "{$balance} {$symbol}";
                            }),
                        TextEntry::make('vendor.type.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.user.type'))
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('vendor.status.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.user.status'))
                            ->badge()
                            ->color('success'),
                        TextEntry::make('created_at')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
