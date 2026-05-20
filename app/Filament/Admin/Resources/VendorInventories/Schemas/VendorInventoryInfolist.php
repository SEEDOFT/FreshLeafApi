<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Schemas;

use App\Models\VendorInventory;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class VendorInventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.vendor_inventory.vendor'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendor_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.full_name').'</strong>'))
                            ->getStateUsing(
                                static fn (VendorInventory $record) => "{$record->vendor->last_name} {$record->vendor->first_name}"
                            ),
                        TextEntry::make('vendor.email')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.email').'</strong>')),
                        TextEntry::make('vendor.phone_number')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.phone').'</strong>')),
                    ]),

                Section::make(__('admin.resources.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name_en')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.name_en').'</strong>')),
                        TextEntry::make('product.name_km')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.name_km').'</strong>')),
                        TextEntry::make('price')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.unit_price').'</strong>'))
                            ->money('USD'),
                        TextEntry::make('stock_quantity')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.stock').'</strong>')),
                        TextEntry::make('unit.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.unit').'</strong>')),
                        TextEntry::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.status').'</strong>'))
                            ->badge(),
                    ]),

                Section::make(__('admin.resources.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('province_of_origin')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.province_of_origin').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('certification_type')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.certification_type').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('farm_location')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.farm_location').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('harvest_date')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.harvest_date').'</strong>'))
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('shelf_life_days')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.shelf_life').'</strong>'))
                            ->suffix(' '.__('admin.resources.product.days'))
                            ->placeholder('-'),
                        TextEntry::make('packaging_type')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.packaging_type').'</strong>'))
                            ->placeholder('-'),
                        ImageEntry::make('batch_images')
                            ->label(new HtmlString('<strong>'.__('admin.resources.product.visuals').'</strong>'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
