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
use Illuminate\Support\HtmlString;

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
                            ->label(new HtmlString('<strong>'.__('shared.product.name_en').'</strong>'))
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('product.name_km')
                            ->label(new HtmlString('<strong>'.__('shared.product.name_km').'</strong>'))
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('product.description_en')
                            ->label(new HtmlString('<strong>'.__('shared.product.description_en').'</strong>'))
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('product.description_km')
                            ->label(new HtmlString('<strong>'.__('shared.product.description_km').'</strong>'))
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        Select::make('product.product_category_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.system_category').'</strong>'))
                            ->relationship('product.productCategory', 'name_en')
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->columnSpanFull(),
                        Select::make('product_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.label').'</strong>'))
                            ->options(
                                Product::get()
                                    ->pluck('translated_name', 'id')
                            )
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->required(),
                        Select::make('currency_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.currency').'</strong>'))
                            ->options(Currency::all()
                                ->pluck('translated_currency', 'id')
                            )
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(new HtmlString('<strong>'.__('shared.product.quantity').'</strong>'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>'))
                            ->options(
                                Unit::all()
                                    ->pluck('translated_name', 'id')
                            )
                            ->required(),
                        Select::make('inventory_status_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.status').'</strong>'))
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
                            ->relationship('packagingType', 'name_en')
                            ->options(PackagingType::all()->pluck('translated_name', 'id')),
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
