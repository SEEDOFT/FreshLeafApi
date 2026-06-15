<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Actions;

use App\Constants\StorageDirectory;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use App\Services\OrderService;
use Filament\Actions\Action;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
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
     * Get the 'Mark as Out for Delivery' action.
     */
    public static function outForDelivery(string $type = 'table'): Action
    {
        $actionClass = Action::class;

        return $actionClass::make('outForDelivery')
            ->label(__('admin.resources.order.actions.out_for_delivery'))
            ->icon(Heroicon::OutlinedTruck)
            ->color('fuchsia')
            ->visible(fn (Order $record) => $record->order_status_id === OrderStatus::PREPARING_ID)
            ->form([
                FileUpload::make('preparation_proof_photo')
                    ->label(__('admin.resources.order.proof_of_preparation'))
                    ->image()
                    ->required()
                    ->directory(StorageDirectory::ORDER_PROOFS),
                TextInput::make('delivery_company_name')
                    ->label(__('admin.resources.order.delivery_company_name'))
                    ->required(),
                Textarea::make('delivery_tracking_info')
                    ->label(__('admin.resources.order.tracking_info_optional')),
            ])
            ->action(function (Order $record, array $data): void {
                $record->update([
                    'order_status_id' => OrderStatus::OUT_FOR_DELIVERY_ID,
                    'order_out_for_delivery_date' => now(),
                    'preparation_proof_photo' => $data['preparation_proof_photo'],
                    'delivery_company_name' => $data['delivery_company_name'],
                    'delivery_tracking_info' => $data['delivery_tracking_info'] ?? null,
                ]);

                Notification::make()
                    ->title(__('admin.resources.order.actions.out_for_delivery_success'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Get the 'Mark as Delivered' action.
     */
    public static function deliver(string $type = 'table'): Action
    {
        $actionClass = Action::class;

        return $actionClass::make('deliver')
            ->label(__('admin.resources.order.actions.deliver'))
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->form([
                FileUpload::make('delivery_proof_photo')
                    ->label(__('admin.resources.order.delivery_proof_photo'))
                    ->image()
                    ->required()
                    ->directory(StorageDirectory::DELIVERY_PROOFS),
            ])
            ->visible(fn (Order $record) => $record->order_status_id === OrderStatus::OUT_FOR_DELIVERY_ID)
            ->action(function (Order $record, array $data): void {
                $record->update([
                    'order_status_id' => OrderStatus::DELIVERED_ID,
                    'delivery_proof_photo' => $data['delivery_proof_photo'],
                ]);

                // Automatically complete COD payments on delivery
                $isCod = $record->payments()
                    ->whereHas(
                        'paymentMethod',
                        static function (Builder $query): void {
                            $query->where(
                                'payment_method_type_id',
                                PaymentMethodType::COD_ID,
                            );
                        },
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
            ->form([
                Textarea::make('cancellation_reason')
                    ->label(__('admin.resources.order.fields.cancellation_reason'))
                    ->required()
                    ->maxLength(255),
            ])
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => \in_array($record->order_status_id, [OrderStatus::PENDING_ID, OrderStatus::CONFIRMED_ID], true))
            ->action(function (Order $record, array $data): void {
                app(OrderService::class)->autoCancelOrder($record, $data['cancellation_reason']);
                Notification::make()
                    ->title(__('admin.resources.order.actions.cancelled_success'))
                    ->success()
                    ->send();
            });
    }
}
