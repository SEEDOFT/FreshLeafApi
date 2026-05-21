<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages\Schemas;

use App\Models\Currency;
use App\Models\PackagingType;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;

class AddToStoreForm
{
    public static function schema(): array
    {
        return [
            TextInput::make('price')
                ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                ->numeric()
                ->required(),
            Select::make('currency_id')
                ->label(new HtmlString('<strong>'.__('shared.product.currency').'</strong>'))
                ->options(Currency::all()->pluck('code', 'id'))
                ->default(Currency::USD_ID)
                ->required(),
            TextInput::make('stock_quantity')
                ->label(new HtmlString('<strong>'.__('shared.product.quantity').'</strong>'))
                ->numeric()
                ->required(),
            Select::make('unit_id')
                ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>'))
                ->options(Unit::all()->pluck('translated_name', 'id'))
                ->required(),
            TextInput::make('province_of_origin')
                ->label(new HtmlString('<strong>'.__('shared.product.province_of_origin').'</strong>')),
            TextInput::make('certification_type')
                ->label(new HtmlString('<strong>'.__('shared.product.certification_type').'</strong>')),
            TextInput::make('farm_location')
                ->label(new HtmlString('<strong>'.__('shared.product.farm_location').'</strong>')),
            DatePicker::make('harvest_date')
                ->label(new HtmlString('<strong>'.__('shared.product.harvest_date').'</strong>')),
            TextInput::make('shelf_life_days')
                ->label(new HtmlString('<strong>'.__('shared.product.shelf_life').'</strong>'))
                ->numeric()
                ->suffix(' '.__('shared.product.days')),
            Select::make('packaging_type_id')
                ->label(new HtmlString('<strong>'.__('shared.product.packaging_type').'</strong>'))
                ->options(PackagingType::all()->pluck('translated_name', 'id')),
        ];
    }
}
