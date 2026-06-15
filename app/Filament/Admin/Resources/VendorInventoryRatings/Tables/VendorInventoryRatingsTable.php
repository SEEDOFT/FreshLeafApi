<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventoryRatings\Tables;

use App\Filament\Admin\Resources\VendorInventories\VendorInventoryResource;
use App\Models\VendorInventoryRating;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VendorInventoryRatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->recordClasses(fn (VendorInventoryRating $record) => match (true) {
                $record->rating >= 4 => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                $record->rating >= 3 => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                default => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
            })
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.fullName')
                    ->label(__('admin.resources.rating.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vendorInventory.vendor.business_name')
                    ->label(__('admin.resources.vendor.business_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('orderItem.order.order_number')
                    ->label(__('admin.resources.order.order_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vendorInventory.product.name_en')
                    ->label(__('admin.resources.rating.product'))
                    ->description(fn ($record) => $record->vendorInventory->product->name_km)
                    ->url(fn ($record) => VendorInventoryResource::getUrl('view', ['record' => $record->vendor_inventory_id]))
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label(__('admin.resources.rating.rating'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->sortable(),

                TextColumn::make('review')
                    ->label(__('admin.resources.rating.review'))
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('shared.rating.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}
