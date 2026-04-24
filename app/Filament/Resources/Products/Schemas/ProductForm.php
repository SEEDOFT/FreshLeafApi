<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

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
                Section::make('General Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),

                Grid::make(3)
                    ->schema([
                        Section::make('Categorization')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                Select::make('product_category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('product_type_id')
                                    ->relationship('type', 'name')
                                    ->required(),
                                Select::make('product_status_id')
                                    ->relationship('status', 'name')
                                    ->required(),
                                Select::make('default_unit_id')
                                    ->relationship('defaultUnit', 'name')
                                    ->required(),
                            ]),

                        Section::make('Attributes')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('shelf_life_days')
                                    ->numeric()
                                    ->suffix('days'),
                                Toggle::make('is_organic')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Nutrition & Data')
                    ->schema([
                        KeyValue::make('nutrition_data')
                            ->addActionLabel('Add Nutrition Fact')
                            ->keyLabel('Fact')
                            ->valueLabel('Value'),
                    ]),
            ]);
    }
}
