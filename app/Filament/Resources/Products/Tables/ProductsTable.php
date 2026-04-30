<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('name_en')
                    ->label(__('admin.resources.product.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_km')
                    ->label(__('admin.resources.product.name_km'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productCategory.name_en')
                    ->label(__('admin.resources.product.system_category'))
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label(__('admin.resources.product.type'))
                    ->sortable(),
                TextColumn::make('status.name')
                    ->label(__('admin.resources.product.status'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_organic')
                    ->label(__('admin.resources.product.is_organic'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('shelf_life_days')
                    ->label(__('admin.resources.product.shelf_life'))
                    ->numeric()
                    ->sortable()
                    ->suffix(' days'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->relationship('productCategory', 'name_en')
                    ->label(__('admin.resources.product.system_category')),
                SelectFilter::make('product_status_id')
                    ->relationship('status', 'name')
                    ->label(__('admin.resources.product.status')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
