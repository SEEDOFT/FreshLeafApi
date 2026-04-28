<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_en')
                            ->label(__('admin.resources.product.name_en'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('name_km')
                            ->label(__('admin.resources.product.name_km'))
                            ->required(),
                        TextInput::make('slug')
                            ->label(__('admin.resources.product.slug'))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description_en')
                            ->label(__('admin.resources.product.description_en'))
                            ->columnSpanFull(),
                        Textarea::make('description_km')
                            ->label(__('admin.resources.product.description_km'))
                            ->columnSpanFull(),
                    ]),

                Grid::make(3)
                    ->schema([
                        Section::make(__('admin.resources.product.categorization'))
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                Select::make('product_category_id')
                                    ->label(__('admin.resources.product.system_category'))
                                    ->relationship('productCategory', 'name_en')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('organic_category_id')
                                    ->label(__('admin.resources.product.organic_category'))
                                    ->relationship('organicCategory', 'name_en')
                                    ->searchable()
                                    ->preload(),
                                Select::make('product_type_id')
                                    ->label(__('admin.resources.product.type'))
                                    ->relationship('type', 'name')
                                    ->required(),
                                Select::make('product_status_id')
                                    ->label(__('admin.resources.product.status'))
                                    ->relationship('status', 'name')
                                    ->required(),
                                Select::make('default_unit_id')
                                    ->label(__('admin.resources.product.unit'))
                                    ->relationship('defaultUnit', 'name')
                                    ->required(),
                                TextInput::make('selling_unit')
                                    ->label(__('admin.resources.product.selling_unit'))
                                    ->placeholder('e.g. kg, bunch'),
                            ]),

                        Section::make(__('admin.resources.product.pricing_inventory'))
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('price_per_unit')
                                    ->label(__('admin.resources.product.price'))
                                    ->numeric()
                                    ->prefix('KHR')
                                    ->minValue(0),
                                TextInput::make('available_stock')
                                    ->label(__('admin.resources.product.stock'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label(__('admin.resources.product.is_active'))
                                    ->default(true),
                                Toggle::make('is_organic')
                                    ->label(__('admin.resources.product.is_organic'))
                                    ->default(true),
                            ]),
                    ]),

                Section::make(__('admin.resources.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('farm_name_location')
                            ->label(__('admin.resources.product.farm_location'))
                            ->placeholder('e.g. Kandal Organic Farm'),
                        Select::make('farming_method')
                            ->label(__('admin.resources.product.farming_method'))
                            ->options([
                                'certified_organic' => __('admin.resources.product.farming_methods.certified_organic'),
                                'pesticide_free' => __('admin.resources.product.farming_methods.pesticide_free'),
                                'naturally_grown' => __('admin.resources.product.farming_methods.naturally_grown'),
                            ]),
                        DatePicker::make('harvest_date')
                            ->label(__('admin.resources.product.harvest_date')),
                        TextInput::make('shelf_life_days')
                            ->label(__('admin.resources.product.shelf_life'))
                            ->numeric()
                            ->suffix('days'),
                    ]),

                Section::make(__('admin.resources.product.nutrition_data'))
                    ->schema([
                        KeyValue::make('nutrition_data')
                            ->label(__('admin.resources.product.nutrition_data'))
                            ->addActionLabel(__('admin.resources.product.add_nutrition'))
                            ->keyLabel(__('admin.resources.product.nutrition_key'))
                            ->valueLabel(__('admin.resources.product.nutrition_value')),
                    ]),
            ]);
    }
}
