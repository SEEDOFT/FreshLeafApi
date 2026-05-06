<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorInventories\Tables;

use App\Models\VendorInventory;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendorInventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('vendor.name')
                    ->label(__('admin.resources.vendor_inventory.vendor'))
                    ->getStateUsing(static fn (VendorInventory $record) => "{$record->vendor->last_name} {$record->vendor->first_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                ImageColumn::make('product.image_url')
                    ->label(__('admin.resources.product.image')),

                TextColumn::make('product.name_en')
                    ->label(__('admin.resources.product.name_en'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('admin.resources.product.unit_price'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label(__('admin.resources.product.stock'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit.name')
                    ->label(__('admin.resources.product.unit')),

                TextColumn::make('province_of_origin')
                    ->label(__('admin.resources.product.province_of_origin'))
                    ->searchable(),

                TextColumn::make('status.name')
                    ->label(__('admin.resources.product.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendor_id')
                    ->label(__('admin.resources.vendor_inventory.vendor'))
                    ->relationship('vendor', 'last_name'),
                SelectFilter::make('product_category_id')
                    ->label(__('admin.resources.product.system_category'))
                    ->relationship('product.productCategory', 'name_en'),
                SelectFilter::make('inventory_status_id')
                    ->label(__('admin.resources.product.status'))
                    ->relationship('status', 'name'),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
