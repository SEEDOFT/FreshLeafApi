<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Tables;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
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
            ->recordClasses(fn (Product $record) => match ($record->product_status_id) {
                ProductStatus::DRAFT_ID => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                ProductStatus::PUBLISHED_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                ProductStatus::ARCHIVED_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                ImageColumn::make('image_url')
                    ->label(__('admin.resources.product.image'))
                    ->getStateUsing(fn ($record) => resolve_image_url($record->image_url))
                    ->circular(),
                TextColumn::make('name_km')
                    ->label(__('admin.resources.product.name_km'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name_en')
                    ->label(__('admin.resources.product.name_en'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('productCategory.translated_name')
                    ->label(__('admin.resources.product.system_category'))
                    ->sortable(),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.product.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('admin.resources.product.system_category'))
                    ->options(fn () => ProductCategory::all()->pluck('translated_name', 'id')),
                SelectFilter::make('product_status_id')
                    ->label(__('admin.resources.product.status'))
                    ->options(fn () => ProductStatus::all()->pluck('translated_name', 'id')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
