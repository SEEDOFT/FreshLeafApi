<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order_number')
                            ->copyable(),
                        TextEntry::make('status.name')
                            ->badge(),
                        TextEntry::make('user.first_name')
                            ->label('Customer'),
                        TextEntry::make('vendor.business_name')
                            ->label('Vendor'),
                    ]),

                Section::make('Financial Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->money('USD'),
                        TextEntry::make('commission_amount')
                            ->money('USD'),
                        TextEntry::make('payment_status.name')
                            ->badge(),
                        TextEntry::make('payment_method.name'),
                    ]),

                Section::make('Delivery Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('delivery_address')
                            ->columnSpanFull(),
                        TextEntry::make('delivery_contact_name'),
                        TextEntry::make('delivery_contact_phone'),
                    ]),

                Section::make('Timestamps')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
