<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Schemas;

use App\Models\VendorInventory;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorInventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.vendor_inventory.vendor'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('admin.resources.user.first_name'))
                            ->getStateUsing(fn (VendorInventory $record): string => $record->vendor->first_name),
                        TextEntry::make('last_name')
                            ->label(__('admin.resources.user.last_name'))
                            ->getStateUsing(fn (VendorInventory $record): string => $record->vendor->last_name),
                        TextEntry::make('vendor.email')
                            ->label(__('admin.resources.user.email')),
                        TextEntry::make('vendor.phone_number')
                            ->label(__('admin.resources.user.phone')),
                    ]),

                Section::make(__('admin.resources.product.pricing_inventory'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name_en')
                            ->label(__('admin.resources.product.name_en')),
                        TextEntry::make('product.name_km')
                            ->label(__('admin.resources.product.name_km')),
                        TextEntry::make('price')
                            ->label(__('admin.resources.product.unit_price')),
                        TextEntry::make('currency.code')
                            ->label(__('shared.product.currency')),
                        TextEntry::make('stock_quantity')
                            ->label(__('admin.resources.product.stock')),
                        TextEntry::make('unit.translated_name')
                            ->label(__('admin.resources.product.unit')),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.product.status'))
                            ->badge(),
                    ]),

                Section::make(__('admin.resources.product.organic_traceability'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('province_of_origin')
                            ->label(__('admin.resources.product.province_of_origin')),
                        TextEntry::make('certification_type')
                            ->label(__('admin.resources.product.certification_type')),
                        TextEntry::make('farm_location')
                            ->label(__('admin.resources.product.farm_location')),
                        TextEntry::make('harvest_date')
                            ->label(__('admin.resources.product.harvest_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('shelf_life_days')
                            ->label(__('admin.resources.product.shelf_life'))
                            ->suffix(' '.__('admin.resources.product.days')),
                        TextEntry::make('packagingType.translated_name')
                            ->label(__('admin.resources.product.packaging_type')),
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
