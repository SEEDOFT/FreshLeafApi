<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryBatches\Tables;

use App\Models\InventoryBatch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InventoryBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('batch_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productVariant.name')
                    ->label('Variant')
                    ->sortable(),
                TextColumn::make('available_qty')
                    ->label('Available')
                    ->getStateUsing(fn (InventoryBatch $record) => $record->received_qty - $record->sold_qty - $record->damaged_qty - $record->expired_qty)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'In Stock' => 'success',
                        'Low Stock' => 'warning',
                        'Out of Stock' => 'danger',
                        'Expired' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->color(fn (InventoryBatch $record) => $record->expiry_date && $record->expiry_date->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name'),
                SelectFilter::make('inventory_batch_status_id')
                    ->relationship('status', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
