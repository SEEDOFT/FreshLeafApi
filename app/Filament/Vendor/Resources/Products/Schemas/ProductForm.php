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
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name_km')
                                    ->label('Name (Khmer)')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $state, $set) => $set('slug', Str::slug($state))),
                                TextInput::make('name_en')
                                    ->label('Name (English)')
                                    ->required(),
                                TextInput::make('slug')
                                    ->hidden()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('product_category_id')
                                    ->label('Category')
                                    ->relationship('productCategory', 'name_en')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('product_type_id')
                                    ->label('Type')
                                    ->relationship('type', 'name')
                                    ->required(),

                                Select::make('default_unit_id')
                                    ->label('Base Unit')
                                    ->relationship('defaultUnit', 'name')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Product Details')
                    ->schema([
                        Textarea::make('description_en')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description_km')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('shelf_life_days')
                                    ->label('Shelf Life (Days)')
                                    ->numeric()
                                    ->suffix('days'),

                                Toggle::make('is_organic')
                                    ->label('Certified Organic')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }
}
