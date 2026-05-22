<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Schemas;

use App\Constants\StorageDirectory;
use App\Models\Currency;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\Unit;
use App\Models\VendorInventoryStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('product.name_en')
                            ->label(__('shared.product.name_en'))
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('product.name_km')
                            ->label(__('shared.product.name_km'))
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('product.description_en')
                            ->label(__('shared.product.description_en'))
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('product.description_km')
                            ->label(__('shared.product.description_km'))
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        Select::make('product.product_category_id')
                            ->label(__('shared.product.system_category'))
                            ->relationship('product.productCategory', 'name_en')
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->columnSpanFull(),
                        Select::make('product_id')
                            ->label(__('shared.product.label'))
                            ->options(
                                Product::get()
                                    ->pluck('translated_name', 'id')
                            )
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label(__('shared.product.unit_price'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->required(),
                        Select::make('currency_id')
                            ->label(__('shared.product.currency'))
                            ->options(Currency::all()
                                ->pluck('translated_currency', 'id')
                            )
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(__('shared.product.quantity'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(__('shared.product.unit'))
                            ->options(
                                Unit::all()
                                    ->pluck('translated_name', 'id')
                            )
                            ->required(),
                        Select::make('inventory_status_id')
                            ->label(__('shared.product.status'))
                            ->options(
                                VendorInventoryStatus::all()
                                    ->pluck('translated_name', 'id')
                            )
                            ->required(),
                    ]),
                Section::make(__('shared.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('province_of_origin')
                            ->label(__('shared.product.province_of_origin'))
                            ->required(),
                        TextInput::make('certification_type')
                            ->label(__('shared.product.certification_type'))
                            ->required(),
                        TextInput::make('farm_location')
                            ->label(__('shared.product.farm_location'))
                            ->required(),
                        DatePicker::make('harvest_date')
                            ->label(__('shared.product.harvest_date'))
                            ->required(),
                        TextInput::make('shelf_life_days')
                            ->label(__('shared.product.shelf_life'))
                            ->numeric()
                            ->suffix(' '.__('shared.product.days'))
                            ->required(),
                        Select::make('packaging_type_id')
                            ->label(__('shared.product.packaging_type'))
                            ->options(PackagingType::all()->pluck('translated_name', 'id'))
                            ->required(),
                        FileUpload::make('batch_images')
                            ->label(__('shared.product.visuals'))
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory(StorageDirectory::PRODUCT_BATCHES)
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }
}
