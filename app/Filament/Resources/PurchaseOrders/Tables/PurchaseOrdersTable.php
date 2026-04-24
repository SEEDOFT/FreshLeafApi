<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('po_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Ordered' => 'warning',
                        'Received' => 'success',
                        'Cancelled' => 'danger',
                        default => 'info',
                    })
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('ordered_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('received_at')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name'),
                SelectFilter::make('purchase_order_status_id')
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
