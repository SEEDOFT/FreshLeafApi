<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

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
                Section::make(__('admin.resources.order.overview'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label(__('admin.resources.order.order_number'))
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('user_id')
                            ->label(__('admin.resources.order.user'))
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('order_type_id')
                            ->label(__('admin.resources.order.type'))
                            ->relationship('type', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('order_status_id')
                            ->label(__('admin.resources.order.status'))
                            ->relationship('status', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('payment_status_id')
                            ->label(__('admin.resources.order.payment_status'))
                            ->relationship('paymentStatus', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('address_id')
                            ->label(__('admin.resources.order.address'))
                            ->relationship('address', 'label')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ]),

                Section::make(__('admin.resources.order.logistics'))
                    ->columns(2)
                    ->schema([
                        DatePicker::make('delivery_date')
                            ->label(__('admin.resources.order.delivery_date')),
                        TextInput::make('delivery_slot')
                            ->label(__('admin.resources.order.delivery_slot')),
                        Textarea::make('notes')
                            ->label(__('admin.resources.order.notes'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(__('admin.resources.order.subtotal'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('discount_amount')
                            ->label(__('admin.resources.order.discount'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('delivery_fee')
                            ->label(__('admin.resources.order.delivery_fee'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('tax_amount')
                            ->label(__('admin.resources.order.tax'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('total_amount')
                            ->label(__('admin.resources.order.total'))
                            ->numeric()
                            ->prefix('$')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ]),
            ]);
    }
}
