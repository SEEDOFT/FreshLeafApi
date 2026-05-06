<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('image_url')
                    ->label(__('admin.resources.product_category.image')),
                TextColumn::make('name_en')
                    ->label(__('admin.resources.product_category.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_km')
                    ->label(__('admin.resources.product_category.name_km'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('admin.resources.product_category.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status.name')
                    ->label(__('admin.resources.product_category.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label(__('admin.resources.product_category.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.product_category.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
