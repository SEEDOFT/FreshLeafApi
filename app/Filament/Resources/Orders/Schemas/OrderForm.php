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
                Section::make('Order Overview')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('user_id')
                            ->relationship('user', 'first_name') // Should probably use a concatenated name if possible
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('order_type_id')
                            ->relationship('type', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('order_status_id')
                            ->relationship('status', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('payment_status_id')
                            ->relationship('paymentStatus', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('address_id')
                            ->relationship('address', 'label')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ]),

                Section::make('Logistics')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('delivery_date'),
                        TextInput::make('delivery_slot'),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Financials')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('discount_amount')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('delivery_fee')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('tax_amount')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ]),
            ]);
    }
}
