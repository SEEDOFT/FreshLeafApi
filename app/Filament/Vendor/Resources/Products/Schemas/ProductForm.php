<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.product.general_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name_km')
                                    ->label(__('admin.resources.product.name_km'))
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $state, $set) => $set('slug', Str::slug($state))),
                                TextInput::make('name_en')
                                    ->label(__('admin.resources.product.name_en'))
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                                TextInput::make('slug')
                                    ->label(__('admin.resources.product.slug'))
                                    ->hidden()
                                    ->dehydrated()
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                                    ->unique(ignoreRecord: true),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('product_category_id')
                                    ->label(__('admin.resources.product.organic_category'))
                                    ->relationship('productCategory', 'name_en')
                                    ->searchable()
                                    ->preload()
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),

                                Select::make('product_type_id')
                                    ->label(__('admin.resources.product.product_type'))
                                    ->relationship('type', 'name')
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),

                                Select::make('default_unit_id')
                                    ->label(__('admin.resources.product.unit'))
                                    ->relationship('defaultUnit', 'name')
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                            ]),
                    ]),

                Section::make(__('admin.resources.product.details'))
                    ->schema([
                        Textarea::make('description_en')
                            ->label(__('admin.resources.product.description_en'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description_km')
                            ->label(__('admin.resources.product.description_km'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('shelf_life_days')
                                    ->label(__('admin.resources.product.shelf_life'))
                                    ->numeric()
                                    ->suffix(' '.__('admin.resources.product.days')),

                                Toggle::make('is_organic')
                                    ->label(__('admin.resources.product.is_organic'))
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }
}
