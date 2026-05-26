<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExchangeRateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fromCurrency.translated_currency')
                    ->label(__('admin.resources.exchange_rate.base_currency')),
                TextEntry::make('toCurrency.translated_currency')
                    ->label(__('admin.resources.exchange_rate.target_currency')),
                TextEntry::make('rate')
                    ->label(__('admin.resources.exchange_rate.rate'))
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('d M Y, h:i A'),
                TextEntry::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('d M Y, h:i A'),
            ]);
    }
}
