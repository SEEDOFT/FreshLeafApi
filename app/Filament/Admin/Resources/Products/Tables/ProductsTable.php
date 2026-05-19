<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Tables;

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
use Illuminate\Support\HtmlString;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('image_url')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.image').'</strong>'))
                    ->disk('public')
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('admin.resources.product.system_category'))
                    ->options(
                        ProductCategory::all()
                            ->pluck('translated_name', 'id')
                    ),
                SelectFilter::make('product_status_id')
                    ->label(__('admin.resources.product.status'))
                    ->options(
                        ProductStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
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
