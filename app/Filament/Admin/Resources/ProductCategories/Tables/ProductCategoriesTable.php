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

        return $table
            ->stackedOnMobile()
            ->recordClasses(fn (ProductCategory $record) => match ($record->product_category_status_id) {
                ProductCategoryStatus::ACTIVE_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                ProductCategoryStatus::INACTIVE_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                ImageColumn::make('image_url')
                    ->label(__('admin.resources.product_category.image'))
                    ->getStateUsing(fn ($record) => resolve_image_url($record->image_url))
                    ->circular(),
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
                TextColumn::make('status.id')
                    ->label(__('admin.resources.product_category.status'))
                    ->badge()
                    ->color(fn (ProductCategory $record): string => $record->status?->getColor() ?? 'gray')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
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

                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.product_category.created_at'))
                    ->dateTime('h:i A, d M Y')

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
