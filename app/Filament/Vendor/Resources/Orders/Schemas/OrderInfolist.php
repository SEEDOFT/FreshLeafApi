<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.order.overview'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label(__('admin.resources.order.order_number'))
                            ->copyable()
                            ->icon('heroicon-o-hashtag'),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.order.status'))
                            ->badge()
                            ->color(fn (Order $record): string => match ($record->status->id) {
                                OrderStatus::PENDING_ID => 'gray',
                                OrderStatus::CONFIRMED_ID => 'info',
                                OrderStatus::PREPARING_ID => 'warning',
                                OrderStatus::OUT_FOR_DELIVERY_ID => 'fuchsia',
                                OrderStatus::DELIVERED_ID => 'success',
                                OrderStatus::CANCELLED_ID => 'danger',
                                OrderStatus::AWAITING_PAYMENT_ID => 'warning',
                                default => 'primary',
                            }),
                        TextEntry::make('type.name')
                            ->label(__('admin.resources.order.order_type'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('user.fullName')
                            ->label(__('admin.resources.order.customer'))
                            ->icon('heroicon-o-user'),
                        TextEntry::make('currency.name')
                            ->label(__('admin.resources.order.currency'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('notes')
                            ->label(__('admin.resources.order.notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->placeholder(__('shared.order.total_placeholder'))
                            ->getStateUsing(function (Order $record): string {
                                $balance = number_format(
                                    (float) $record->total_amount,
                                    2,
                                );
                                $symbol = $record->currency->symbol ?? '$';

                                return $record->currency?->id === Currency::USD_ID
                                    ? "$symbol $balance"
                                    : "$balance $symbol";
                            })
                            ->weight('bold'),
                        TextEntry::make('subtotal')
                            ->label(__('admin.resources.order.subtotal'))
                            ->money('USD'),
                        TextEntry::make('discount_amount')
                            ->label(__('admin.resources.order.discount'))
                            ->money('USD'),
                        TextEntry::make('delivery_fee')
                            ->label(__('admin.resources.order.delivery_fee'))
                            ->money('USD'),
                        TextEntry::make('tax_amount')
                            ->label(__('admin.resources.order.tax'))
                            ->money('USD'),
                        TextEntry::make('vendor_commission')
                            ->label(__('admin.resources.order.commission'))
                            ->state(
                                fn ($record) => $record->items
                                    ->filter(
                                        fn ($item) => $item->vendorInventory
                                            ->vendor_id === Auth::id()
                                    )
                                    ->sum('commission_amount')
                            )
                            ->money('USD')
                            ->badge()
                            ->color('danger'),
                        TextEntry::make('vendor_net')
                            ->label(__('admin.resources.order.net_earnings'))
                            ->state(
                                fn ($record) => $record->items
                                    ->filter(
                                        fn ($item) => $item->vendorInventory
                                            ->vendor_id === Auth::id()
                                    )
                                    ->sum('vendor_net_amount')
                            )
                            ->money('USD')
                            ->badge()
                            ->color('success'),
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
                        TextEntry::make('payment.paymentMethod.name')
                            ->label(__('admin.resources.order.payment_method'))
                            ->placeholder('—'),
                    ]),

                Section::make(__('admin.resources.order.payment_details'))
                    ->columns(3)
                    ->visible(fn (Order $record) => $record->payment !== null)
                    ->schema([
                        TextEntry::make('payment.payment_number')
                            ->label(__('admin.resources.order.payment_number'))
                            ->copyable()
                            ->icon('heroicon-o-credit-card'),
                        TextEntry::make('payment.amount')
                            ->label(__('admin.resources.order.payment_amount'))
                            ->getStateUsing(function (Order $record): string {
                                $payment = $record->payment;
                                if (! $payment) {
                                    return '—';
                                }

                                $amount = number_format(
                                    (float) $payment->amount,
                                    2,
                                );
                                $symbol = $payment->currency->symbol ?? '$';

                                return $payment->currency?->id === Currency::KHR_ID
                                    ? "$amount $symbol"
                                    : "$symbol $amount";
                            }),
                        TextEntry::make('payment.paid_at')
                            ->label(__('admin.resources.order.paid_at'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('exchangeRateHistory.rate')
                            ->label(__('admin.resources.order.exchange_rate_used'))
                            ->prefix('$1 = ')
                            ->suffix(' KHR')
                            ->badge()
                            ->color('info')
                            ->visible(
                                fn (Order $record) => $record
                                    ->exchange_rate_history_id !== null
                            ),
                    ]),

                Section::make(__('admin.resources.order.delivery_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('address.label')
                            ->label(__('admin.resources.order.address_label'))
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('—'),
                        TextEntry::make('delivery_date')
                            ->label(__('admin.resources.order.delivery_date'))
                            ->date('d M Y'),
                        TextEntry::make('delivery_slot')
                            ->label(__('admin.resources.order.delivery_slot')),
                        TextEntry::make('address.recipient_name')
                            ->label(__('admin.resources.order.delivery_contact_name'))
                            ->icon('heroicon-o-user'),
                        TextEntry::make('address.phone')
                            ->label(__('admin.resources.order.delivery_contact_phone'))
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('full_delivery_address')
                            ->label(__('admin.resources.order.delivery_address'))
                            ->state(fn (Order $record): string => $record->address->address)
                            ->columnSpanFull(),
                        TextEntry::make('Address map')
                            ->label(__('admin.resources.order.delivery_address_map'))
                            ->state(fn (Order $record): string => $record->address->address_map ?? '—')
                            ->url(fn (Order $record): ?string => $record->address->address_map)
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-map-pin')
                            ->color('info')
                            ->extraAttributes([
                                'class' => 'underline',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.order.order_items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->columns(5)
                            ->schema([
                                TextEntry::make('product_name_snapshot')
                                    ->label(__('admin.resources.order.product')),
                                TextEntry::make('quantity')
                                    ->label(__('admin.resources.order.qty'))
                                    ->suffix(
                                        fn ($record) => ' '
                                            .$record->unit_snapshot
                                    ),
                                TextEntry::make('unit_price_snapshot')
                                    ->label(__('admin.resources.order.unit_price'))
                                    ->money('USD'),
                                TextEntry::make('subtotal')
                                    ->label(__('admin.resources.order.subtotal'))
                                    ->money('USD'),
                                TextEntry::make('vendor_net_amount')
                                    ->label(__('admin.resources.order.net'))
                                    ->money('USD'),
                            ]),
                    ]),

                Section::make(__('admin.resources.order.dispatch_info'))
                    ->visible(
                        fn (Order $record) => $record
                            ->order_out_for_delivery_date !== null
                    )
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('preparation_proof_photo')
                            ->label(__('admin.resources.order.proof_of_preparation'))
                            ->columnSpanFull(),
                        TextEntry::make('delivery_company_name')
                            ->label(__('admin.resources.order.delivery_company'))
                            ->icon('heroicon-o-truck')
                            ->placeholder('—'),
                        TextEntry::make('delivery_tracking_info')
                            ->label(__('admin.resources.order.tracking_info'))
                            ->icon('heroicon-o-map')
                            ->placeholder('—'),
                    ]),

                Section::make(__('admin.resources.timestamps'))
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('place_order_date')
                            ->label(__('admin.resources.order.place_order_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_pending_date')
                            ->label(__('admin.resources.order.pending_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_confirmed_date')
                            ->label(__('admin.resources.order.confirmed_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_preparing_date')
                            ->label(__('admin.resources.order.preparing_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_out_for_delivery_date')
                            ->label(__('admin.resources.order.out_for_delivery_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_delivered_date')
                            ->label(__('admin.resources.order.delivered_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_cancelled_date')
                            ->label(__('admin.resources.order.cancelled_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('order_awaiting_payment_date')
                            ->label(__('admin.resources.order.awaiting_payment_date'))
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
