<?php

namespace App\Filament\Vendor\Resources\Payouts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vendor_user_id')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->numeric(),
                TextInput::make('payout_status_id')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->numeric(),
                TextInput::make('payout_method_id')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->numeric(),
                TextInput::make('amount')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->numeric(),
                TextInput::make('transaction_reference'),
                DateTimePicker::make('processed_at'),
                TextInput::make('processed_by_admin_id')
                    ->numeric(),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }
}
