<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Actions;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use Filament\Actions\Action as PageAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class OrderActions
{
    /**
     * Get the 'Accept Order' action.
     */
    public static function accept(string $type = 'table'): PageAction
    {
        $actionClass = PageAction::class;

        return $actionClass::make('accept')
            ->label(__('admin.resources.order.actions.accept'))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->order_status_id === OrderStatus::PENDING_ID)
            ->action(function (Order $record): void {
                $record->update(['order_status_id' => OrderStatus::CONFIRMED_ID]);

                Notification::make()
                    ->title(__('admin.resources.order.actions.accepted_success'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Get the 'Start Preparing' action.
     */
    public static function prepare(string $type = 'table'): PageAction
    {
        $actionClass = PageAction::class;

        return $actionClass::make('prepare')
            ->label(__('admin.resources.order.actions.prepare'))
            ->icon(Heroicon::OutlinedClock)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->order_status_id === OrderStatus::CONFIRMED_ID)
            ->action(function (Order $record): void {
                $record->update(['order_status_id' => OrderStatus::PREPARING_ID]);

                Notification::make()
                    ->title(__('admin.resources.order.actions.prepared_success'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Get the 'Mark as Delivered' action.
     */
    public static function deliver(string $type = 'table'): PageAction
    {
        $actionClass = PageAction::class;

        return $actionClass::make('deliver')
            ->label(__('admin.resources.order.actions.deliver'))
            ->icon(Heroicon::OutlinedTruck)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => $record->order_status_id === OrderStatus::PREPARING_ID)
            ->action(function (Order $record): void {
                $record->update(['order_status_id' => OrderStatus::DELIVERED_ID]);

                // Automatically complete COD payments on delivery
                $isCod = $record->payments()
                    ->whereHas(
                        'paymentMethod',
                        static fn ($q) => $q->where(
                            'payment_method_type_id',
                            PaymentMethodType::COD_ID,
                        ),
                    )
                    ->exists();

                if (
                    $isCod &&
                    $record->payment_status_id === PaymentStatus::PENDING_ID
                ) {
                    $record->update([
                        'payment_status_id' => PaymentStatus::COMPLETED_ID,
                    ]);

                    $record->payments()
                        ->where('status_id', PaymentStatus::PENDING_ID)
                        ->update([
                            'status_id' => PaymentStatus::COMPLETED_ID,
                            'paid_at' => Carbon::now(),
                        ]);
                }

                Notification::make()
                    ->title(__('admin.resources.order.actions.delivered_success'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Get the 'Cancel Order' action.
     */
    public static function cancel(string $type = 'table'): PageAction
    {
        $actionClass = PageAction::class;

        return $actionClass::make('cancel')
            ->label(__('admin.resources.order.actions.cancel'))
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Order $record) => in_array($record->order_status_id, [OrderStatus::PENDING_ID, OrderStatus::CONFIRMED_ID], true))
            ->action(function (Order $record): void {
                $record->update(['order_status_id' => OrderStatus::CANCELLED_ID]);

                Notification::make()
                    ->title(__('admin.resources.order.actions.cancelled_success'))
                    ->success()
                    ->send();
            });
    }
}
