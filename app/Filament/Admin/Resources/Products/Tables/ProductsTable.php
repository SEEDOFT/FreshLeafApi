<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Tables;

use App\Models\Product;
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
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.product.name'))
                    ->getStateUsing(fn (Product $record): string => $record->localizedName)
                    ->sortable(query: function (Builder $query, string $direction) {
                        $column = app()->getLocale() === 'km' ? 'name_km' : 'name_en';
                        $query->orderBy($column, $direction);
                    })
                    ->searchable(['name_en', 'name_km']),
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
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
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
