<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ExchangeRateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fromCurrency.translated_currency')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.exchange_rate.base_currency')),
                TextEntry::make('toCurrency.translated_currency')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.exchange_rate.target_currency')),
                TextEntry::make('rate')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.exchange_rate.rate'))
                    ->numeric(),
                TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime(),
            ]);
    }
}
