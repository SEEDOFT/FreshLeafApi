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
                    ->label(new HtmlString('<strong>'.__('shared.payout.amount').'</strong>'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('transaction_reference')
                    ->label(new HtmlString('<strong>'.__('shared.payout.transaction_ref').'</strong>'))
                    ->placeholder('-'),
                DateTimePicker::make('processed_at')
                    ->label(new HtmlString('<strong>'.__('shared.payout.processed_at').'</strong>')),
                Textarea::make('admin_notes')
                    ->label(new HtmlString('<strong>'.__('shared.payout.admin_notes').'</strong>'))
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
