<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use App\Models\Currency;
use App\Models\Order;
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
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.order.status'))
                            ->badge(),
                        TextEntry::make('user.fullName')
                            ->label(__('admin.resources.order.customer')),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->placeholder('0.00')
                            ->getStateUsing(function (Order $record): string {
                                $balance = number_format((float) $record->total_amount, 2);
                                $symbol = $record->currency?->symbol;

                                return $record->currency?->id === Currency::USD_ID
                                    ? "$symbol $balance"
                                    : "$balance $symbol";
                            }),
                        TextEntry::make('commission_amount')
                            ->label(__('admin.resources.order.commission'))
                            ->badge(),
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
