<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->label(__('shared.payout.amount'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('shared.payout.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Processing' => 'info',
                        'Completed' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('method.name')
                    ->label(__('shared.payout.method')),

                TextColumn::make('transaction_reference')
                    ->label(__('shared.payout.reference'))
                    ->searchable(),

                TextColumn::make('processed_at')
                    ->label(__('shared.payout.paid_on'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
