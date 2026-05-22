<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use App\Models\ProductCategoryStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->components([
                Section::make(__('admin.resources.product_category.basic_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name_en')
                            ->label(__('admin.resources.product_category.name_en')),
                        TextEntry::make('name_km')
                            ->label(__('admin.resources.product_category.name_km')),
                        TextEntry::make('slug')
                            ->label(__('admin.resources.product_category.slug')),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.product_category.status'))
                            ->badge()
                            ->placeholder($notProvided)
                            ->color(fn (ProductCategory $record): string => match ($record->product_category_status_id) {
                                ProductCategoryStatus::ACTIVE_ID => 'success',
                                ProductCategoryStatus::INACTIVE_ID => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('description_en')
                            ->label(__('admin.resources.product_category.description_en'))
                            ->columnSpanFull(),
                        TextEntry::make('description_km')
                            ->label(__('admin.resources.product_category.description_km'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.resources.product_category.visuals'))
                    ->schema([
                        ImageEntry::make('image_url')
                            ->label(__('admin.resources.product_category.image'))
                            ->imageSize(200),
                    ]),
            ]);
    }
}
