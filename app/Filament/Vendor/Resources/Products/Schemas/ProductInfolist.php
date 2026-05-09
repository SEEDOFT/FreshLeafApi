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
                Section::make(__('admin.resources.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.productCategory.name_en')
                            ->label(__('admin.resources.product.system_category')),
                        TextEntry::make('product.description_en')
                            ->label(__('admin.resources.product.description_en'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                        TextEntry::make('product.description_km')
                            ->label(__('admin.resources.product.description_km'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),

                Section::make(__('admin.resources.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name_en')
                            ->label(__('admin.resources.product.name_en')),
                        TextEntry::make('product.name_km')
                            ->label(__('admin.resources.product.name_km')),
                        TextEntry::make('price')
                            ->label(__('admin.resources.product.unit_price'))
                            ->money('USD'),
                        TextEntry::make('stock_quantity')
                            ->label(__('admin.resources.product.stock')),
                        TextEntry::make('unit.name')
                            ->label(__('admin.resources.product.unit')),
                        TextEntry::make('status.name')
                            ->label(__('admin.resources.product.status'))
                            ->badge(),
                    ]),

                Section::make(__('admin.resources.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('province_of_origin')
                            ->label(__('admin.resources.product.province_of_origin'))
                            ->placeholder('-'),
                        TextEntry::make('certification_type')
                            ->label(__('admin.resources.product.certification_type'))
                            ->placeholder('-'),
                        TextEntry::make('farm_location')
                            ->label(__('admin.resources.product.farm_location'))
                            ->placeholder('-'),
                        TextEntry::make('harvest_date')
                            ->label(__('admin.resources.product.harvest_date'))
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('shelf_life_days')
                            ->label(__('admin.resources.product.shelf_life'))
                            ->suffix(' '.__('admin.resources.product.days'))
                            ->placeholder('-'),
                        TextEntry::make('packaging_type')
                            ->label(__('admin.resources.product.packaging_type'))
                            ->placeholder('-'),
                        ImageEntry::make('batch_images')
                            ->label(__('admin.resources.product.visuals'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
