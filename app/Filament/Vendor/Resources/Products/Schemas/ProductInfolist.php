<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.productCategory.name_en')
                            ->label(__('shared.product.system_category')),
                        TextEntry::make('product.description_en')
                            ->label(__('shared.product.description_en'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                        TextEntry::make('product.description_km')
                            ->label(__('shared.product.description_km'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),

                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name_en')
                            ->label(__('shared.product.name_en')),
                        TextEntry::make('product.name_km')
                            ->label(__('shared.product.name_km')),
                        TextEntry::make('price')
                            ->label(__('shared.product.unit_price'))
                            ->money('USD'),
                        TextEntry::make('stock_quantity')
                            ->label(__('shared.product.stock')),
                        TextEntry::make('unit.name')
                            ->label(__('shared.product.unit')),
                        TextEntry::make('status.name')
                            ->label(__('shared.product.status'))
                            ->badge(),
                    ]),

                Section::make(__('shared.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('province_of_origin')
                            ->label(__('shared.product.province_of_origin'))
                            ->placeholder('-'),
                        TextEntry::make('certification_type')
                            ->label(__('shared.product.certification_type'))
                            ->placeholder('-'),
                        TextEntry::make('farm_location')
                            ->label(__('shared.product.farm_location'))
                            ->placeholder('-'),
                        TextEntry::make('harvest_date')
                            ->label(__('shared.product.harvest_date'))
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('shelf_life_days')
                            ->label(__('shared.product.shelf_life'))
                            ->suffix(' '.__('shared.product.days'))
                            ->placeholder('-'),
                        TextEntry::make('packaging_type')
                            ->label(__('shared.product.packaging_type'))
                            ->placeholder('-'),
                        ImageEntry::make('batch_images')
                            ->label(__('shared.product.visuals'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
