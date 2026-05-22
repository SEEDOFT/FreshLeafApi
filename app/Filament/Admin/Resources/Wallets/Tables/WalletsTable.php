<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Tables;

use App\Models\Currency;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.user.full_name'))
                    ->getStateUsing(static fn (Wallet $record) => $record->user->fullName)
                    ->placeholder($notProvided)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('currency.translated_currency')

                    ->label(__('admin.resources.wallet.currency'))
                    ->placeholder($notProvided)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('balance')
                    ->label(__('admin.resources.wallet.balance'))
                    ->placeholder($notProvided)
                    ->getStateUsing(static function (Wallet $record): string {
                        $id = $record->currency->id;
                        $symbol = $record->currency->symbol ?? '';
                        $balance = number_format((float) $record->balance, 2);

                        return $id === Currency::USD_ID
                            ? "{$symbol} {$balance}"
                            : "{$balance} {$symbol}";
                    })
                    ->sortable(),
                TextColumn::make('user.type.translated_name')

                    ->label(__('admin.resources.user.type'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->color(fn (Wallet $record): string => match ($record->user->type->id) {
                        UserType::ADMIN_ID => 'success',
                        UserType::VENDOR_ID => 'info',
                        UserType::CONSUMER_ID => 'warning',
                        default => 'secondary',
                    }),
                TextColumn::make('user.status.translated_name')

                    ->label(__('admin.resources.user.status'))
                    ->badge()
                    ->color(fn (Wallet $record): string => match ($record->user->status->id) {
                        UserStatus::ACTIVE_ID => 'success',
                        UserStatus::PENDING_ID => 'warning',
                        UserStatus::INACTIVE_ID, UserStatus::DELETED_ID => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('updated_at')

                    ->label(__('admin.resources.updated_at'))
                    ->placeholder($notProvided)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('currency_id')
                    ->label(__('admin.resources.wallet.currency'))
                    ->options(
                        Currency::all()
                            ->pluck('translated_currency', 'id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
