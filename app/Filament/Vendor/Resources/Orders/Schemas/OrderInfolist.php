<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderStatus;
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
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label(__('admin.resources.order.order_number'))
                            ->copyable(),
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
                                default => 'primary',
                            }),
                        TextEntry::make('user.fullName')
                            ->label(__('admin.resources.order.customer')),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->placeholder(__('shared.order.total_placeholder'))
                            ->getStateUsing(function (Order $record): string {
                                $balance = number_format((float) $record->total_amount, 2);
                                $symbol = $record->currency?->symbol;

                                return $record->currency?->id === Currency::USD_ID
                                    ? "$symbol $balance"
                                    : "$balance $symbol";
                            }),
                        TextEntry::make('commission_amount')
                            ->label(__('admin.resources.order.commission'))
                            ->state(fn ($record) => $record->items->filter(fn ($item) => $item->vendorInventory->vendor_id === Auth::id())->sum('commission_amount'))
                            ->badge(),
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
                            ->dateTime(),
                        TextEntry::make('order_awaiting_payment_date')
                            ->label(__('admin.resources.order.awaiting_payment_date'))
                            ->dateTime(),
                        TextEntry::make('order_out_for_delivery_date')
                            ->label('Out for Delivery Date')
                            ->dateTime(),
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime(),
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
