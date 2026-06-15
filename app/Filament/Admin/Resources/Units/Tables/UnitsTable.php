<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordClasses(fn () => 'bg-gray-50 dark:bg-gray-900/50 border-l-4 border-gray-400')
            ->columns([
                TextColumn::make('name_km')
                    ->label(__('admin.resources.unit.name_km'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_en')
                    ->label(__('admin.resources.unit.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('symbol')
                    ->label(__('admin.resources.unit.symbol'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('conversion_to_base')
                    ->label(__('admin.resources.unit.conversion'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
