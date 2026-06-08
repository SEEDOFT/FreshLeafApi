<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.product.general_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name_en')
                            ->label(__('admin.resources.product.name_en')),
                        TextEntry::make('name_km')
                            ->label(__('admin.resources.product.name_km')),
                        TextEntry::make('slug')
                            ->label(__('admin.resources.product.slug')),
                        TextEntry::make('productCategory.translated_name')
                            ->label(__('admin.resources.product.system_category')),
                        TextEntry::make('type.translated_name')
                            ->label(__('admin.resources.product.type')),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.product.status'))
                            ->badge(),
                        TextEntry::make('defaultUnit.translated_name')
                            ->label(__('admin.resources.product.unit')),
                        TextEntry::make('description_en')
                            ->label(__('admin.resources.product.description_en'))
                            ->columnSpanFull(),
                        TextEntry::make('description_km')
                            ->label(__('admin.resources.product.description_km'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.product.nutrition_data'))
                    ->schema([
                        KeyValue::make('nutrition_data')
                            ->label(__('admin.resources.product.nutrition_data'))
                            ->keyLabel(__('admin.resources.product.nutrition_key'))
                            ->valueLabel(__('admin.resources.product.nutrition_value')),
                    ]),

                Section::make(__('admin.resources.product.visuals'))
                    ->schema([
                        ImageEntry::make('image_url')
                            ->label(__('admin.resources.product.image'))

                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                    ]),
            ]);
    }
}
