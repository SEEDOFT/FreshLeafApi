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
                TextInput::make('amount')
                    ->label(__('admin.resources.payout.amount'))
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('transaction_reference')
                    ->label(__('admin.resources.payout.transaction_ref'))
                    ->placeholder('-'),
                DateTimePicker::make('processed_at')
                    ->label(__('admin.resources.payout.processed_at')),
                Textarea::make('admin_notes')
                    ->label(__('admin.resources.payout.admin_notes'))
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
