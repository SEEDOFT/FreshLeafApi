<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->components([
                Section::make(__('shared.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.productCategory.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.system_category')),
                        TextEntry::make('product.description_en')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.description_en'))
                            ->columnSpanFull(),
                        TextEntry::make('product.description_km')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.description_km'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name_en')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.name_en')),
                        TextEntry::make('product.name_km')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.name_km')),
                        TextEntry::make('price')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.unit_price')),
                        TextEntry::make('currency.code')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.currency')),
                        TextEntry::make('stock_quantity')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.stock')),
                        TextEntry::make('unit.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.unit')),
                        TextEntry::make('status.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.status'))
                            ->badge(),
                    ]),

                Section::make(__('shared.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('province_of_origin')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.province_of_origin')),
                        TextEntry::make('certification_type')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.certification_type')),
                        TextEntry::make('farm_location')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.farm_location')),
                        TextEntry::make('harvest_date')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.harvest_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('shelf_life_days')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.shelf_life'))
                            ->suffix(' '.__('shared.product.days')),
                        TextEntry::make('packagingType.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.packaging_type')),
                    ]),
                Section::make(__('shared.product.visuals'))
                    ->schema([
                        ViewEntry::make('batch_images')
                            ->view('filament.infolists.components.horizontal-image-scroll')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
