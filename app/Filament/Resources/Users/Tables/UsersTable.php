<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
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
                    ->getStateUsing(static fn (User $record) => "{$record->last_name} {$record->first_name}")
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('email')
                    ->label(__('admin.resources.user.email'))
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label(__('admin.resources.user.phone'))
                    ->searchable(),
                TextColumn::make('type.name')
                    ->label(__('admin.resources.user.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'Vendor' => 'warning',
                        'Consumer' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('status.name')
                    ->label(__('admin.resources.user.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Pending' => 'warning',
                        'Inactive', 'Deleted' => 'danger',
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
                    ->relationship('type', 'name')
                    ->label(__('admin.resources.user.account_type')),
                SelectFilter::make('user_status_id')
                    ->relationship('status', 'name')
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
