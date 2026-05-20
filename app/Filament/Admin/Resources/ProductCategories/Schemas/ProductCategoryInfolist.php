<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use App\Models\ProductCategoryStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                        TextEntry::make('name_en')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.name_en').'</strong>')),
                        TextEntry::make('name_km')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.name_km').'</strong>')),
                        TextEntry::make('slug')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.slug').'</strong>')),
                        TextEntry::make('status.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.status').'</strong>'))
                            ->badge()
                            ->placeholder($notProvided)
                            ->color(fn (ProductCategory $record): string => match ($record->product_category_status_id) {
                                ProductCategoryStatus::ACTIVE_ID => 'success',
                                ProductCategoryStatus::INACTIVE_ID => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('description_en')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.description_en').'</strong>'))
                            ->columnSpanFull(),
                        TextEntry::make('description_km')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.description_km').'</strong>'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.resources.product_category.visuals'))
                    ->schema([
                        ImageEntry::make('image_url')
                            ->label(new HtmlString('<strong>'.__('admin.resources.product_category.image').'</strong>'))
                            ->imageSize(200),
                    ]),
            ]);
    }
}
