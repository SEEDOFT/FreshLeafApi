<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Tables;

use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use Filament\Actions\Action;
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
                    ->getStateUsing(fn (VendorInventory $record) => $record->vendor->fullName)
                    ->searchable(['first_name', 'last_name']),
                ImageColumn::make('product.image_url')
                    ->label(__('admin.resources.product.image')),
                TextColumn::make('product.name_en')

                    ->label(__('admin.resources.product.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')

                    ->label(__('admin.resources.product.unit_price'))
                    ->money(fn (VendorInventory $record) => $record->currency->code)
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
                TextColumn::make('status.translated_name')

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
                    ->options(
                        VendorInventoryStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (VendorInventory $record): bool => $record->inventory_status_id === VendorInventoryStatus::PENDING_REVIEW_ID)
                    ->action(function (VendorInventory $record): void {
                        $record->update([
                            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Approve Inventory')
                    ->modalDescription('Are you sure you want to approve this inventory and make it available in the store?')
                    ->modalSubmitActionLabel('Approve'),
            ]);
    }
}
