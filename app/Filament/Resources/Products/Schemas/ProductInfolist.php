<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name_en')
                            ->label(__('admin.resources.product.name_en')),
                        TextEntry::make('name_km')
                            ->label(__('admin.resources.product.name_km')),
                        TextEntry::make('productCategory.name_en')
                            ->label(__('admin.resources.product.organic_category')),
                        TextEntry::make('type.name')
                            ->label(__('admin.resources.product.product_type')),
                    ]),
                Section::make(__('admin.resources.product.pricing_inventory'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('defaultUnit.name')
                            ->label(__('admin.resources.product.unit')),
                        TextEntry::make('price_per_unit')
                            ->label(__('admin.resources.product.price'))
                            ->money('USD'),
                        TextEntry::make('available_stock')
                            ->label(__('admin.resources.product.stock'))
                            ->numeric(),
                    ]),
                Section::make(__('admin.resources.product.general_info'))
                    ->schema([
                        TextEntry::make('description_en')
                            ->label(__('admin.resources.product.description_en'))
                            ->columnSpanFull(),
                        TextEntry::make('description_km')
                            ->label(__('admin.resources.product.description_km'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
