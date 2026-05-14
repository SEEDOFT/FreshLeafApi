<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
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
            ->stackedOnMobile()
            ->recordAction('view')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.user.full_name'))
                    ->getStateUsing(fn (User $record): string => $record->fullName),
                TextColumn::make('email')
                    ->label(__('admin.resources.user.email'))
                    ->placeholder('N/A')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label(__('admin.resources.user.phone'))
                    ->searchable(),
                TextColumn::make('type.id')
                    ->label(__('admin.resources.user.type'))
                    ->badge()
                    ->state(fn (User $record): string => $record->type->name_en)
                    ->color(fn (User $record): string => match ($record->type->id) {
                        UserType::ADMIN_ID => 'danger',
                        UserType::VENDOR_ID => 'warning',
                        UserType::CONSUMER_ID => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('status.id')
                    ->label(__('admin.resources.user.status'))
                    ->badge()
                    ->state(fn (User $record): string => $record->status->name_en)
                    ->color(fn (User $record): string => match ($record->status->id) {
                        UserStatus::ACTIVE_ID => 'success',
                        UserStatus::PENDING_ID => 'warning',
                        UserStatus::INACTIVE_ID, UserStatus::DELETED_ID => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_type_id')
                    ->relationship('type', 'name_en')
                    ->label(__('admin.resources.user.account_type')),
                SelectFilter::make('user_status_id')
                    ->relationship('status', 'name_en')
                    ->label(__('admin.resources.user.account_status')),
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
