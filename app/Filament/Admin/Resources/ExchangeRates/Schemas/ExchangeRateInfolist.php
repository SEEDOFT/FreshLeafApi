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
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.base_currency').'</strong>')),
                TextEntry::make('toCurrency.translated_currency')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.target_currency').'</strong>')),
                TextEntry::make('rate')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.rate').'</strong>'))
                    ->numeric(),
                TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                    ->dateTime(),
            ]);
    }
}
