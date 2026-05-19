<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Schemas;

use App\Models\VendorInventoryStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('product.description_en')
                            ->label(__('shared.product.description_en'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('product.description_km')
                            ->label(__('shared.product.description_km'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        Select::make('product.product_category_id')
                            ->label(__('shared.product.system_category'))
                            ->relationship('product.productCategory', 'name_en')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Select::make('product_id')
                            ->label(__('shared.product.label'))
                            ->relationship('product', 'name_en')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label(__('shared.product.unit_price'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(__('shared.product.quantity'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(__('shared.product.unit'))
                            ->relationship('unit', 'name')
                            ->required(),
                        Select::make('inventory_status_id')
                            ->label(__('shared.product.status'))
                            ->relationship('status', 'name')
                            ->required()
                            ->default(VendorInventoryStatus::AVAILABLE_ID),
                    ]),

                Section::make(__('shared.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
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
                        TextInput::make('packaging_type')
                            ->label(__('shared.product.packaging_type')),
                        FileUpload::make('batch_images')
                            ->label(__('shared.product.visuals'))
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('vendor-inventory')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
