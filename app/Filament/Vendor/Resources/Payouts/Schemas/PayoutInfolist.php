<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.payout.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('amount')
                            ->label(__('shared.payout.amount'))
                            ->money('USD'),
                        TextEntry::make('status.name')
                            ->label(__('shared.payout.status'))
                            ->badge(),
                        TextEntry::make('method.name')
                            ->label(__('shared.payout.method')),
                        TextEntry::make('transaction_reference')
                            ->label(__('shared.payout.transaction_ref'))
                            ->placeholder('-'),
                        TextEntry::make('processed_at')
                            ->label(__('shared.payout.processed_at'))
                            ->dateTime('h:i A, d M Y')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label(__('shared.payout.admin_notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('shared.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('shared.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
