<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.order.overview'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label(__('admin.resources.order.order_number'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('user.name')
                            ->label(__('admin.resources.order.customer'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('user.phone_number')
                            ->label(__('admin.resources.user.phone'))
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('order_type_id')
                            ->label(__('admin.resources.order.type'))
                            ->relationship('type', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('order_status_id')
                            ->label(__('admin.resources.order.status'))
                            ->relationship('status', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('payment_status_id')
                            ->label(__('admin.resources.order.payment_status'))
                            ->relationship('paymentStatus', 'name')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make(__('admin.resources.order.delivery_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('delivery_address_label')
                            ->label(__('admin.resources.order.delivery_address'))
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('delivery_date')
                            ->label(__('admin.resources.order.delivery_date'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivery_slot')
                            ->label(__('admin.resources.order.delivery_slot'))
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label(__('admin.resources.order.notes'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(__('admin.resources.order.subtotal'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('discount_amount')
                            ->label(__('admin.resources.order.discount'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivery_fee')
                            ->label(__('admin.resources.order.delivery_fee'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('tax_amount')
                            ->label(__('admin.resources.order.tax'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
