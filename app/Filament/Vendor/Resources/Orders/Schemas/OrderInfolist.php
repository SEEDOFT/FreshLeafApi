<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use Filament\Infolists\Components\ImageEntry;
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
                        TextEntry::make('type.translated_name')
                            ->label(__('admin.resources.order.order_type'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('user.fullName')
                            ->label(__('admin.resources.order.customer'))
                            ->icon('heroicon-o-user'),
                        TextEntry::make('currency.translated_currency')
                            ->label(__('admin.resources.order.currency'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('notes')
                            ->label(__('admin.resources.order.notes'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->placeholder(__('shared.order.total_placeholder'))
                            ->formatStateUsing(fn (Order $record): string => Order::formatMoney($record->total_amount, $record->currency))
                            ->weight('bold'),
                        TextEntry::make('subtotal')
                            ->label(__('admin.resources.order.subtotal'))
                            ->formatStateUsing(fn (Order $record): string => Order::formatMoney($record->subtotal, $record->currency)),
                        TextEntry::make('discount_amount')
                            ->label(__('admin.resources.order.discount'))
                            ->formatStateUsing(fn (Order $record): string => Order::formatMoney($record->discount_amount, $record->currency)),
                        TextEntry::make('delivery_fee')
                            ->label(__('admin.resources.order.delivery_fee'))
                            ->formatStateUsing(fn (Order $record): string => Order::formatMoney($record->delivery_fee, $record->currency)),
                        TextEntry::make('tax_amount')
                            ->label(__('admin.resources.order.tax'))
                            ->formatStateUsing(fn (Order $record): string => Order::formatMoney($record->tax_amount, $record->currency)),
                        TextEntry::make('vendor_commission')
                            ->label(__('admin.resources.order.commission'))
                            ->state(fn (Order $record): float => $record->items
                                ->filter(
                                    fn ($item) => $item->vendorInventory
                                        ->vendor_id === Auth::id()
                                )
                                ->sum('commission_amount'))
                            ->formatStateUsing(fn (float $state, Order $record): string => Order::formatMoney($state, $record->currency))
                            ->badge()
                            ->color('danger'),
                        TextEntry::make('vendor_net')
                            ->label(__('admin.resources.order.net_earnings'))
                            ->state(fn (Order $record): float => $record->items
                                ->filter(
                                    fn ($item) => $item->vendorInventory
                                        ->vendor_id === Auth::id()
                                )
                                ->sum('vendor_net_amount'))
                            ->formatStateUsing(fn (float $state, Order $record): string => Order::formatMoney($state, $record->currency))
                            ->badge()
                            ->color('success'),
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
                        TextEntry::make('payment.currency.translated_currency')
                            ->label(__('admin.resources.order.payment_currency'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('paymentStatus.translated_name')
                            ->label(__('admin.resources.order.payment_status'))
                            ->badge()
                            ->color(fn (Order $record): string => match ($record->paymentStatus->id) {
                                PaymentStatus::PENDING_ID => 'info',
                                PaymentStatus::COMPLETED_ID => 'success',
                                PaymentStatus::FAILED_ID => 'danger',
                                PaymentStatus::REFUNDED_ID => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('payment.paymentMethod.type.translated_name')
                            ->label(__('admin.resources.order.payment_method'))
                            ->badge()
                            ->color(fn (Order $record): string => match ($record->payment?->paymentMethod?->payment_method_type_id) {
                                PaymentMethodType::WALLET_ID => 'primary',
                                PaymentMethodType::CREDIT_DEBIT_ID => 'info',
                                PaymentMethodType::ABA_ID => 'success',
                                PaymentMethodType::ACLEDA_ID => 'warning',
                                PaymentMethodType::COD_ID => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('payment.paid_at')
                            ->label(__('admin.resources.order.paid_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('exchange_rate_used')
                            ->label(__('admin.resources.order.exchange_rate_used'))
                            ->getStateUsing(function (Order $record): ?string {
                                $history = $record->exchangeRateHistory;
                                if (! $history) {
                                    return null;
                                }

                                $rateValue = (float) $history->rate;
                                $decimals = $rateValue < 1 ? 8 : 2;
                                $rate = number_format($rateValue, $decimals);

                                $fromSymbol = $history->fromCurrency->id === Currency::USD_ID ? '$' : '៛';
                                $toSymbol = $history->toCurrency->id === Currency::KHR_ID ? '៛' : '$';

                                $fromAmount = $history->fromCurrency->id === Currency::KHR_ID ? "1{$fromSymbol}" : "{$fromSymbol}1";
                                $toAmount = $history->toCurrency->id === Currency::KHR_ID ? "{$rate}{$toSymbol}" : "{$toSymbol}{$rate}";

                                return "{$fromAmount} = {$toAmount}";
                            })
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
                            ->icon('heroicon-o-map-pin'),
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

                Section::make(__('admin.resources.order.dispatch_info'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('preparation_proof_photo')
                            ->label(__('admin.resources.order.proof_of_preparation'))
                            ->columnSpanFull(),
                        TextEntry::make('delivery_company_name')
                            ->label(__('admin.resources.order.delivery_company'))
                            ->icon('heroicon-o-truck'),
                        TextEntry::make('delivery_tracking_info')
                            ->label(__('admin.resources.order.tracking_info'))
                            ->icon('heroicon-o-map'),
                    ]),

                Section::make(__('admin.resources.timestamps'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('place_order_date')
                            ->label(__('admin.resources.order.place_order_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_pending_date')
                            ->label(__('admin.resources.order.pending_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_confirmed_date')
                            ->label(__('admin.resources.order.confirmed_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_preparing_date')
                            ->label(__('admin.resources.order.preparing_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_out_for_delivery_date')
                            ->label(__('admin.resources.order.out_for_delivery_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_delivered_date')
                            ->label(__('admin.resources.order.delivered_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_cancelled_date')
                            ->label(__('admin.resources.order.cancelled_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('order_awaiting_payment_date')
                            ->label(__('admin.resources.order.awaiting_payment_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
