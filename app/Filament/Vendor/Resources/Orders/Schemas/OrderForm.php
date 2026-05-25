<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.order.overview'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label(__('shared.order.order_number'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('user.name')
                            ->label(__('shared.order.customer'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('user.phone_number')
                            ->label(__('shared.user.phone'))
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('order_type_id')
                            ->label(__('shared.order.type'))
                            ->relationship('type', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('order_status_id')
                            ->label(__('shared.order.status'))
                            ->relationship('status', 'name'),
                        Select::make('payment_status_id')
                            ->label(__('shared.order.payment_status'))
                            ->relationship('paymentStatus', 'name'),
                    ]),

                Section::make(__('shared.order.delivery_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('delivery_address_label')
                            ->label(__('shared.order.delivery_address'))
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('delivery_date')
                            ->label(__('shared.order.delivery_date'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivery_slot')
                            ->label(__('shared.order.delivery_slot'))
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label(__('shared.order.notes'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(__('shared.order.subtotal'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('discount_amount')
                            ->label(__('shared.order.discount'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivery_fee')
                            ->label(__('shared.order.delivery_fee'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('tax_amount')
                            ->label(__('shared.order.tax'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('total_amount')
                            ->label(__('shared.order.total'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
