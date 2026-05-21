<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Schemas;

use App\Constants\StorageDirectory;
use App\Models\VendorInventoryStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                            ->label(new HtmlString('<strong>'.__('shared.product.description_en').'</strong>'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('product.description_km')
                            ->label(new HtmlString('<strong>'.__('shared.product.description_km').'</strong>'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        Select::make('product.product_category_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.system_category').'</strong>'))
                            ->relationship('product.productCategory', 'name_en')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Select::make('product_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.label').'</strong>'))
                            ->relationship('product', 'name_en')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(new HtmlString('<strong>'.__('shared.product.quantity').'</strong>'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>'))
                            ->relationship('unit', 'name')
                            ->required(),
                        Select::make('inventory_status_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.status').'</strong>'))
                            ->relationship('status', 'name')
                            ->required()
                            ->default(VendorInventoryStatus::AVAILABLE_ID),
                    ]),

                Section::make(__('shared.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
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
                        TextInput::make('packaging_type')
                            ->label(new HtmlString('<strong>'.__('shared.product.packaging_type').'</strong>')),
                        FileUpload::make('batch_images')
                            ->label(new HtmlString('<strong>'.__('shared.product.visuals').'</strong>'))
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory(StorageDirectory::PRODUCT_BATCHES)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
