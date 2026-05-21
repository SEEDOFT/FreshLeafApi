<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ProductInventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.productCategory.translated_name')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.system_category').'</strong>')),
                        TextEntry::make('product.description_en')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.description_en').'</strong>'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                        TextEntry::make('product.description_km')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.description_km').'</strong>'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
                Section::make(__('shared.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name_en')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.name_en').'</strong>')),
                        TextEntry::make('product.name_km')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.name_km').'</strong>')),
                        TextEntry::make('price')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                            ->money('USD'),
                        TextEntry::make('stock_quantity')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.stock').'</strong>')),
                        TextEntry::make('unit.translated_name')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>')),
                        TextEntry::make('status.translated_name')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.status').'</strong>'))
                            ->badge(),
                    ]),

                Section::make(__('shared.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('province_of_origin')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.province_of_origin').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('certification_type')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.certification_type').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('farm_location')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.farm_location').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('harvest_date')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.harvest_date').'</strong>'))
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('shelf_life_days')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.shelf_life').'</strong>'))
                            ->suffix(' '.__('shared.product.days'))
                            ->placeholder('-'),
                        TextEntry::make('packaging_type')
                            ->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.product.packaging_type').'</strong>'))
                            ->placeholder('-'),
                        ImageEntry::make('batch_images')
                            ->label(new HtmlString('<strong>'.__('shared.product.visuals').'</strong>'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
