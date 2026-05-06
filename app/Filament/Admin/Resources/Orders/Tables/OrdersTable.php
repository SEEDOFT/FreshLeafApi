<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordAction('view')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('admin.resources.order.order_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.resources.order.customer'))
                    ->getStateUsing(fn (Order $record) => "{$record->user?->first_name} {$record->user?->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('status.name')
                    ->label(__('admin.resources.order.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'info',
                        'Processing' => 'warning',
                        'Delivered', 'Completed' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('paymentStatus.name')
                    ->label(__('admin.resources.order.payment_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Paid' => 'success',
                        'Unpaid' => 'danger',
                        'Partial' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('admin.resources.order.total'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label(__('admin.resources.order.delivery_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_status_id')
                    ->label(__('admin.resources.order.status'))
                    ->relationship('status', 'name'),
                SelectFilter::make('payment_status_id')
                    ->label(__('admin.resources.order.payment_status'))
                    ->relationship('paymentStatus', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
