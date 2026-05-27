<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Tables;

use App\Filament\Vendor\Resources\Orders\Actions\OrderActions;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('shared.order.order_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.resources.order.customer'))
                    ->getStateUsing(fn (Order $record) => $record->user?->fullName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.order.status'))
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->status->id) {
                        OrderStatus::PENDING_ID => 'info',
                        OrderStatus::CONFIRMED_ID => 'warning',
                        OrderStatus::PREPARING_ID => 'warning',
                        OrderStatus::DELIVERED_ID => 'success',
                        OrderStatus::CANCELLED_ID => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('paymentStatus.translated_name')
                    ->label(__('admin.resources.order.payment_status'))
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->paymentStatus->id) {
                        PaymentStatus::PENDING_ID => 'info',
                        PaymentStatus::COMPLETED_ID => 'success',
                        PaymentStatus::FAILED_ID => 'danger',
                        PaymentStatus::REFUNDED_ID => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('shared.order.total'))
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label(__('shared.order.delivery_date'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('shared.created_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('shared.updated_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_status_id')
                    ->label(__('shared.order.status'))
                    ->options(
                        OrderStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
                SelectFilter::make('payment_status_id')
                    ->label(__('shared.order.payment_status'))
                    ->options(
                        PaymentStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
            ])
            ->recordActions([
                OrderActions::accept(),
                OrderActions::prepare(),
                OrderActions::deliver(),
                OrderActions::cancel(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
