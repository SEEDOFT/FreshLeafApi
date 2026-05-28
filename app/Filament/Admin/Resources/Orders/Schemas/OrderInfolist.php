<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Schemas;

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
                            ->badge(),
                        TextEntry::make('user.first_name')
                            ->label(__('admin.resources.order.customer')),
                        TextEntry::make('vendor.business_name')
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
                            ->money('USD'),
                        TextEntry::make('payment_status.name')
                            ->label(__('admin.resources.order.payment_status'))
                            ->badge(),
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
                            ->dateTime(),
                        TextEntry::make('order_pending_date')
                            ->label(__('admin.resources.order.pending_date'))
                            ->dateTime(),
                        TextEntry::make('order_confirmed_date')
                            ->label(__('admin.resources.order.confirmed_date'))
                            ->dateTime(),
                        TextEntry::make('order_preparing_date')
                            ->label(__('admin.resources.order.preparing_date'))
                            ->dateTime(),
                        TextEntry::make('order_delivered_date')
                            ->label(__('admin.resources.order.delivered_date'))
                            ->dateTime(),
                        TextEntry::make('order_cancelled_date')
                            ->label(__('admin.resources.order.cancelled_date'))
                            ->dateTime(),
                        TextEntry::make('order_awaiting_payment_date')
                            ->label(__('admin.resources.order.awaiting_payment_date'))
                            ->dateTime(),
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
