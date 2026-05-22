<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('shared.payout.amount'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('shared.payout.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Processing' => 'info',
                        'Completed' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('method.name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('shared.payout.method')),

                TextColumn::make('transaction_reference')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('shared.payout.reference'))
                    ->searchable(),

                TextColumn::make('processed_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('shared.payout.paid_on'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
