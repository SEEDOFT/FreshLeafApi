<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('product.image_url')
                    ->label(__('admin.resources.product.image')),

                TextColumn::make('product.name_en')
                    ->label(__('admin.resources.product.name_en'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name_km')
                    ->label(__('admin.resources.product.name_km'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('admin.resources.product.unit_price'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label(__('admin.resources.product.stock'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit.name')
                    ->label(__('admin.resources.product.unit')),

                TextColumn::make('status.name')
                    ->label(__('admin.resources.product.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('admin.resources.product.system_category'))
                    ->relationship('product.productCategory', 'name_en'),
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
