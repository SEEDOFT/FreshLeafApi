<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordAction('view')
            ->columns([
                TextColumn::make('order_number')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.order_number').'</strong>'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.customer').'</strong>'))
                    ->getStateUsing(fn (Order $record) => $record->user?->fullName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.status').'</strong>'))
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
                TextColumn::make('paymentStatus.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.payment_status').'</strong>'))
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->paymentStatus->id) {
                        PaymentStatus::PENDING_ID => 'info',
                        PaymentStatus::COMPLETED_ID => 'success',
                        PaymentStatus::FAILED_ID => 'danger',
                        PaymentStatus::REFUNDED_ID => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total_amount')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.total').'</strong>'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('delivery_date')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_date').'</strong>'))
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_status_id')
                    ->label(new HtmlString('<strong>'.__('shared.order.status').'</strong>'))
                    ->options(
                        OrderStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
                SelectFilter::make('payment_status_id')
                    ->label(new HtmlString('<strong>'.__('shared.order.payment_status').'</strong>'))
                    ->options(
                        PaymentStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
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
