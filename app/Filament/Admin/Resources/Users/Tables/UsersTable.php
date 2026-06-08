<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Models\User;
use App\Models\UserStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->recordClasses(fn (User $record) => match ($record->user_status_id) {
                UserStatus::PENDING_ID => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                UserStatus::ACTIVE_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                UserStatus::INACTIVE_ID => 'bg-gray-50 dark:bg-gray-900/50 border-l-4 border-gray-400',
                UserStatus::REJECTED_ID, UserStatus::DELETED_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->stackedOnMobile()
            ->recordAction('view')
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('admin.resources.user.first_name'))
                    ->getStateUsing(fn (User $record): string => $record->first_name),
                TextColumn::make('last_name')
                    ->label(__('admin.resources.user.last_name'))
                    ->getStateUsing(fn (User $record): string => $record->last_name),
                TextColumn::make('email')
                    ->label(__('admin.resources.user.email'))

                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label(__('admin.resources.user.phone'))
                    ->searchable(),
                TextColumn::make('type.translated_name')
                    ->label(__('admin.resources.user.type'))
                    ->badge()

                    ->color('info'),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.user.status'))
                    ->badge()
                    ->color(fn (User $record): string => match ($record->status->id) {
                        UserStatus::ACTIVE_ID => 'success',
                        UserStatus::PENDING_ID => 'warning',
                        UserStatus::INACTIVE_ID, UserStatus::REJECTED_ID, UserStatus::DELETED_ID => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_status_id')
                    ->label(__('admin.resources.user.account_status'))
                    ->options(
                        UserStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
