<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_en')
                    ->label(__('admin.resources.product.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_km')
                    ->label(__('admin.resources.product.name_km'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name_en')
                    ->label(__('admin.resources.product.organic_category'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('defaultUnit.name')
                    ->label(__('admin.resources.product.unit')),

                IconColumn::make('is_organic')
                    ->label(__('admin.resources.product.is_organic'))
                    ->boolean(),

                TextColumn::make('status.name')
                    ->label(__('admin.resources.product.status'))
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
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
