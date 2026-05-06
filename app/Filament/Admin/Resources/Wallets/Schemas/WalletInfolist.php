<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Schemas;

use App\Models\Currency;
use App\Models\Wallet;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.wallet.label'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('user_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.full_name').'</strong>'))
                            ->getStateUsing(static fn (Wallet $record) => $record->user ? "{$record->user->last_name} {$record->user->first_name}" : '-'),
                        TextEntry::make('user.email')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.email').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('currency.code')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.currency').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('balance')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.balance').'</strong>'))
                            ->getStateUsing(static function (Wallet $record): string {
                                $id = $record->currency->id ?? null;
                                $symbol = $record->currency->symbol ?? '';
                                $balance = number_format((float) $record->balance, 2);

                                return $id === Currency::USD_ID
                                    ? "{$symbol} {$balance}"
                                    : "{$balance} {$symbol}";
                            }),
                        TextEntry::make('created_at')
                            ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
