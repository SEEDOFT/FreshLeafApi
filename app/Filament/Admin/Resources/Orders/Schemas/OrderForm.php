<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Schemas;

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
                Section::make(__('admin.resources.order.overview'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.order_number').'</strong>'))
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('user_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.user').'</strong>'))
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('order_type_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.type').'</strong>'))
                            ->relationship('type', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('order_status_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.status').'</strong>'))
                            ->relationship('status', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('payment_status_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.payment_status').'</strong>'))
                            ->relationship('paymentStatus', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('address_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.address').'</strong>'))
                            ->relationship('address', 'label')
                            ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),

                Section::make(__('admin.resources.order.logistics'))
                    ->columns(2)
                    ->schema([
                        DatePicker::make('delivery_date')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_date').'</strong>')),
                        TextInput::make('delivery_slot')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_slot').'</strong>')),
                        Textarea::make('notes')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.notes').'</strong>'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.subtotal').'</strong>'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('discount_amount')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.discount').'</strong>'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('delivery_fee')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_fee').'</strong>'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('tax_amount')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.tax').'</strong>'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('total_amount')
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.total').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),
            ]);
    }
}
