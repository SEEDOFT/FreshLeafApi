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
                TextEntry::make('productCategory.name_en')
                    ->label(__('admin.resources.product.organic_category')),
                TextEntry::make('type.name')
                    ->label(__('admin.resources.product.product_type')),
                TextEntry::make('defaultUnit.name')
                    ->label(__('admin.resources.product.unit')),
                TextEntry::make('status.name')
                    ->label(__('admin.resources.product.product_status')),
                TextEntry::make('name_en')
                    ->label(__('admin.resources.product.name_en')),
                TextEntry::make('name_km')
                    ->label(__('admin.resources.product.name_km')),
                TextEntry::make('slug')
                    ->label(__('admin.resources.product.slug')),
                TextEntry::make('description_en')
                    ->label(__('admin.resources.product.description_en'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('description_km')
                    ->label(__('admin.resources.product.description_km'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('nutrition_data')
                    ->label(__('admin.resources.product.nutrition_data'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('shelf_life_days')
                    ->label(__('admin.resources.product.shelf_life'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->label(__('admin.resources.deleted_at'))
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_organic')
                    ->label(__('admin.resources.product.is_organic'))
                    ->boolean(),
            ]);
    }
}
