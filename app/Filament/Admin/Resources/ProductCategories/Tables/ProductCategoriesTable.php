<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductCategories\Tables;

use App\Models\ProductCategory;
use App\Models\ProductCategoryStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $table
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('image_url')
                    ->label(__('admin.resources.product_category.image'))
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name_en')
                    ->label(__('admin.resources.product_category.name_en'))
                    ->placeholder($notProvided)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_km')
                    ->label(__('admin.resources.product_category.name_km'))
                    ->placeholder($notProvided)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('admin.resources.product_category.slug'))
                    ->placeholder($notProvided)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.product_category.status'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->color(fn (ProductCategory $record): string => match ($record->product_category_status_id) {
                        ProductCategoryStatus::ACTIVE_ID => 'success',
                        ProductCategoryStatus::INACTIVE_ID => 'danger',
                        default => 'gray',
                    })
                    ->sortable(query: static function (Builder $query, string $direction): Builder {
                        $locale = App::getLocale();

                        return $query->orderBy(
                            ProductCategoryStatus::select("name_{$locale}")
                                ->whereColumn(
                                    'product_category_statuses.id',
                                    'product_categories.product_category_status_id'
                                )
                                ->limit(1),
                            $direction === 'desc' ? 'desc' : 'asc'
                        );
                    }),
                TextColumn::make('products_count')
                    ->label(__('admin.resources.product_category.products_count'))
                    ->counts('products')
                    ->placeholder($notProvided)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.product_category.created_at'))
                    ->dateTime()
                    ->placeholder($notProvided)
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
