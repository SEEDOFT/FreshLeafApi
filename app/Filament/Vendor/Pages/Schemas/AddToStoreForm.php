<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages\Schemas;

use App\Constants\StorageDirectory;
use App\Models\Currency;
use App\Models\PackagingType;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class AddToStoreForm
{
    public static function schema(): array
    {
        return [
            TextInput::make('price')
                ->label(__('shared.product.unit_price'))
                ->numeric()
                ->required(),
            Select::make('currency_id')
                ->label(__('shared.product.currency'))
                ->options(Currency::all()->pluck('code', 'id'))
                ->default(Currency::USD_ID)
                ->required(),
            TextInput::make('stock_quantity')
                ->label(__('shared.product.quantity'))
                ->numeric()
                ->required(),
            Select::make('unit_id')
                ->label(__('shared.product.unit'))
                ->options(Unit::all()->pluck('translated_name', 'id'))
                ->required(),
            TextInput::make('province_of_origin')
                ->label(__('shared.product.province_of_origin')),
            TextInput::make('certification_type')
                ->label(__('shared.product.certification_type')),
            TextInput::make('farm_location')
                ->label(__('shared.product.farm_location')),
            DatePicker::make('harvest_date')
                ->label(__('shared.product.harvest_date')),
            TextInput::make('shelf_life_days')
                ->label(__('shared.product.shelf_life'))
                ->numeric()
                ->suffix(' '.__('shared.product.days')),
            Select::make('packaging_type_id')
                ->label(__('shared.product.packaging_type'))
                ->options(PackagingType::all()->pluck('translated_name', 'id')),
            FileUpload::make('batch_images')
                ->label(__('shared.product.visuals'))
                ->multiple()
                ->image()
                ->disk('public')
                ->directory(StorageDirectory::PRODUCT_BATCHES)
                ->columnSpanFull(),
        ];
    }
}
