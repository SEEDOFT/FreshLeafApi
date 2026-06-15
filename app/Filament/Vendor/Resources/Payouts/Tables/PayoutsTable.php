<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Tables;

use App\Models\Payout;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->recordClasses(fn (Payout $record) => match ($record->status_id) {
                Payout::STATUS_PENDING => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                Payout::STATUS_PAID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                Payout::STATUS_FAILED => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                TextColumn::make('amount')
                    ->label(__('shared.payout.amount'))
                    ->formatStateUsing(fn (Payout $record): string => format_currency(
                        $record->amount,
                        $record->currency->code
                    ))
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('shared.payout.status'))
                    ->badge()
                    ->color(fn (Payout $record): string => $record->status?->getColor() ?? 'gray'),

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
