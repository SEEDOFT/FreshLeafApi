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
                Section::make(__('admin.resources.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label(__('admin.resources.product.label'))
                            ->relationship('product', 'name_en')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label(__('admin.resources.product.unit_price'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(__('admin.resources.product.quantity'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(__('admin.resources.product.unit'))
                            ->relationship('unit', 'name')
                            ->required(),
                        Select::make('inventory_status_id')
                            ->label(__('admin.resources.product.status'))
                            ->relationship('status', 'name')
                            ->required()
                            ->default(VendorInventoryStatus::ACTIVE),
                    ]),

                Section::make(__('admin.resources.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('province_of_origin')
                            ->label(__('admin.resources.product.province_of_origin')),
                        TextInput::make('certification_type')
                            ->label(__('admin.resources.product.certification_type')),
                        TextInput::make('farm_location')
                            ->label(__('admin.resources.product.farm_location')),
                        DatePicker::make('harvest_date')
                            ->label(__('admin.resources.product.harvest_date')),
                        TextInput::make('shelf_life_days')
                            ->label(__('admin.resources.product.shelf_life'))
                            ->numeric()
                            ->suffix(' '.__('admin.resources.product.days')),
                        TextInput::make('packaging_type')
                            ->label(__('admin.resources.product.packaging_type')),
                        FileUpload::make('batch_images')
                            ->label(__('admin.resources.product.visuals'))
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('vendor-inventory')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
