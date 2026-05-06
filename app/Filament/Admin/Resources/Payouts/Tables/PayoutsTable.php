<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->columns([
                TextColumn::make('vendor.vendorProfile.business_name')
                    ->label(__('admin.resources.payout.business'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label(__('admin.resources.payout.amount'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('admin.resources.payout.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Processing' => 'info',
                        'Completed' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('method.name')
                    ->label(__('admin.resources.payout.method')),

                TextColumn::make('processed_at')
                    ->label(__('admin.resources.payout.processed_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
