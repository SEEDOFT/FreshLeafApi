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
use Illuminate\Support\HtmlString;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $table
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('image_url')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.image').'</strong>'))
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name_en')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.name_en').'</strong>'))
                    ->placeholder($notProvided)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_km')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.name_km').'</strong>'))
                    ->placeholder($notProvided)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.slug').'</strong>'))
                    ->placeholder($notProvided)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status.translated_name')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.status').'</strong>'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->color(fn (ProductCategory $record): string => match ($record->product_category_status_id) {
                        ProductCategoryStatus::ACTIVE_ID => 'success',
                        ProductCategoryStatus::INACTIVE_ID => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.products_count').'</strong>'))
                    ->counts('products')
                    ->placeholder($notProvided)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product_category.created_at').'</strong>'))
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
