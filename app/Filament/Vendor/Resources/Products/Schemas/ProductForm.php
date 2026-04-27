<?php

namespace App\Filament\Vendor\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_category_id')
                    ->relationship('productCategory', 'name')
                    ->required(),
                TextInput::make('product_type_id')
                    ->required()
                    ->numeric(),
                Select::make('default_unit_id')
                    ->relationship('defaultUnit', 'name')
                    ->required(),
                TextInput::make('product_status_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('nutrition_data')
                    ->columnSpanFull(),
                TextInput::make('shelf_life_days')
                    ->numeric(),
                Toggle::make('is_organic')
                    ->required(),
            ]);
    }
}
