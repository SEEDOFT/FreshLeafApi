<?php

namespace App\Filament\Vendor\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('productCategory.name')
                    ->label('Product category'),
                TextEntry::make('product_type_id')
                    ->numeric(),
                TextEntry::make('defaultUnit.name')
                    ->label('Default unit'),
                TextEntry::make('product_status_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('nutrition_data')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('shelf_life_days')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_organic')
                    ->boolean(),
            ]);
    }
}
