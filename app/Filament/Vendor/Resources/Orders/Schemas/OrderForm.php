<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                            ->label(new HtmlString('<strong>'.__('shared.order.order_number').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('user.name')
                            ->label(new HtmlString('<strong>'.__('shared.order.customer').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('user.phone_number')
                            ->label(new HtmlString('<strong>'.__('shared.user.phone').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('order_type_id')
                            ->label(new HtmlString('<strong>'.__('shared.order.type').'</strong>'))
                            ->relationship('type', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('order_status_id')
                            ->label(new HtmlString('<strong>'.__('shared.order.status').'</strong>'))
                            ->relationship('status', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('payment_status_id')
                            ->label(new HtmlString('<strong>'.__('shared.order.payment_status').'</strong>'))
                            ->relationship('paymentStatus', 'name')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make(__('shared.order.delivery_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('delivery_address_label')
                            ->label(new HtmlString('<strong>'.__('shared.order.delivery_address').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('delivery_date')
                            ->label(new HtmlString('<strong>'.__('shared.order.delivery_date').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivery_slot')
                            ->label(new HtmlString('<strong>'.__('shared.order.delivery_slot').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label(new HtmlString('<strong>'.__('shared.order.notes').'</strong>'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(new HtmlString('<strong>'.__('shared.order.subtotal').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('discount_amount')
                            ->label(new HtmlString('<strong>'.__('shared.order.discount').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('delivery_fee')
                            ->label(new HtmlString('<strong>'.__('shared.order.delivery_fee').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('tax_amount')
                            ->label(new HtmlString('<strong>'.__('shared.order.tax').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('total_amount')
                            ->label(new HtmlString('<strong>'.__('shared.order.total').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
