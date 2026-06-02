<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordClasses(fn (Order $record) => match ($record->status->id) {
                OrderStatus::PENDING_ID => 'bg-gray-50 dark:bg-gray-900/50 border-l-4 border-gray-400',
                OrderStatus::CONFIRMED_ID => 'bg-blue-50 dark:bg-blue-900/50 border-l-4 border-blue-400',
                OrderStatus::PREPARING_ID => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                OrderStatus::OUT_FOR_DELIVERY_ID => 'bg-fuchsia-50 dark:bg-fuchsia-900/50 border-l-4 border-fuchsia-400',
                OrderStatus::DELIVERED_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                OrderStatus::CANCELLED_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->recordAction('view')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('admin.resources.order.order_number'))
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
                        OrderStatus::PENDING_ID => 'gray',
                        OrderStatus::CONFIRMED_ID => 'info',
                        OrderStatus::PREPARING_ID => 'warning',
                        OrderStatus::OUT_FOR_DELIVERY_ID => 'fuchsia',
                        OrderStatus::DELIVERED_ID => 'success',
                        OrderStatus::CANCELLED_ID => 'danger',
                        default => 'primary',
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                        OrderStatus::select('name_'.App::getLocale())
                            ->whereColumn('order_statuses.id', 'orders.order_status_id')
                            ->limit(1),
                        strtolower($direction) === 'desc' ? 'desc' : 'asc',
                    )),
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
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                        PaymentStatus::select('name_'.App::getLocale())
                            ->whereColumn('payment_statuses.id', 'orders.payment_status_id')
                            ->limit(1),
                        strtolower($direction) === 'desc' ? 'desc' : 'asc',
                    )),
                TextColumn::make('total_amount')
                    ->label(__('admin.resources.order.total'))
                    ->sortable()
                    ->formatStateUsing(fn (Order $record): string => Order::formatMoney($record->total_amount, $record->currency)),
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
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
