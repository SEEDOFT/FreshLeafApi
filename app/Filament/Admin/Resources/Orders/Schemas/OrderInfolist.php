<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Schemas;

use App\Models\CommissionFee;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.order.overview'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label(__('admin.resources.order.order_number'))
                            ->copyable(),
                        TextEntry::make('status.name')
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
                            }),
                        TextEntry::make('user.fullName')
                            ->label(__('admin.resources.order.customer')),
                        TextEntry::make('vendor.fullName')
                            ->label(__('admin.resources.order.vendor')),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->money('USD'),
                        TextEntry::make('commission_amount')
                            ->label(__('admin.resources.order.commission'))
                            ->state(fn ($record) => $record->items->sum('commission_amount'))
                            ->money('USD'),
                        TextEntry::make('commissionFeeHistory.rate')
                            ->label(__('admin.resources.order.commission_rate_used'))
                            ->suffix('%')
                            ->default(fn () => CommissionFee::current()->rate)
                            ->badge()
                            ->color('info'),
                        TextEntry::make('payment_status.name')
                            ->label(__('admin.resources.order.payment_status'))
                            ->badge()
                            ->color(fn (Order $record): string => match ($record->paymentStatus->id) {
                                PaymentStatus::PENDING_ID => 'info',
                                PaymentStatus::COMPLETED_ID => 'success',
                                PaymentStatus::FAILED_ID => 'danger',
                                PaymentStatus::REFUNDED_ID => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('payment_method.name')
                            ->label(__('admin.resources.order.payment_method')),
                    ]),

                Section::make(__('admin.resources.order.delivery_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('delivery_address')
                            ->label(__('admin.resources.order.delivery_address'))
                            ->columnSpanFull(),
                        TextEntry::make('delivery_contact_name')
                            ->label(__('admin.resources.order.delivery_contact_name')),
                        TextEntry::make('delivery_contact_phone')
                            ->label(__('admin.resources.order.delivery_contact_phone')),
                    ]),

                Section::make(__('admin.resources.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('place_order_date')
                            ->label(__('admin.resources.order.place_order_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_pending_date')
                            ->label(__('admin.resources.order.pending_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_confirmed_date')
                            ->label(__('admin.resources.order.confirmed_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_preparing_date')
                            ->label(__('admin.resources.order.preparing_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_delivered_date')
                            ->label(__('admin.resources.order.delivered_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_cancelled_date')
                            ->label(__('admin.resources.order.cancelled_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_awaiting_payment_date')
                            ->label(__('admin.resources.order.awaiting_payment_date'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('order_out_for_delivery_date')
                            ->label('Out for Delivery Date')
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('d M Y, h:i A'),
                    ]),

                Section::make('Dispatch Information')
                    ->visible(fn (Order $record) => $record->order_out_for_delivery_date !== null)
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('preparation_proof_photo')
                            ->label('Proof of Preparation')
                            ->columnSpanFull(),
                        TextEntry::make('delivery_company_name')
                            ->label('Delivery Company'),
                        TextEntry::make('delivery_tracking_info')
                            ->label('Tracking Info'),
                    ]),
            ]);
    }
}
