<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Schemas;

use App\Models\VendorInventory;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
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
                        TextEntry::make('vendor_name')
                            ->label(__('admin.resources.user.full_name'))
                            ->getStateUsing(
                                static fn (VendorInventory $record) => $record->vendor->fullName
                            ),
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
                            ->label(__('admin.resources.product.province_of_origin')),
                        TextEntry::make('certification_type')
                            ->label(__('admin.resources.product.certification_type')),
                        TextEntry::make('farm_location')
                            ->label(__('admin.resources.product.farm_location')),
                        TextEntry::make('harvest_date')
                            ->label(__('admin.resources.product.harvest_date'))
                            ->date(),
                        TextEntry::make('shelf_life_days')
                            ->label(__('admin.resources.product.shelf_life'))
                            ->suffix(' '.__('admin.resources.product.days')),
                        TextEntry::make('packaging_type')
                            ->label(__('admin.resources.product.packaging_type')),
                        ImageEntry::make('batch_images')
                            ->label(__('shared.product.visuals'))
                            ->disk('public')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
