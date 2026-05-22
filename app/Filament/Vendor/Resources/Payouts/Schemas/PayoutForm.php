<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label(__('shared.payout.amount'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('transaction_reference')
                    ->label(__('shared.payout.transaction_ref'))
                    ->placeholder('-'),
                DateTimePicker::make('processed_at')
                    ->label(__('shared.payout.processed_at')),
                Textarea::make('admin_notes')
                    ->label(__('shared.payout.admin_notes'))
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
