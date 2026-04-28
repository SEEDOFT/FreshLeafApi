<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make(__('admin.resources.product_category.basic_info'))
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('name_en')
                                    ->label(__('admin.resources.product_category.name_en'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                TextInput::make('name_km')
                                    ->label(__('admin.resources.product_category.name_km'))
                                    ->required(),
                                TextInput::make('slug')
                                    ->label(__('admin.resources.product_category.slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Toggle::make('is_active')
                                    ->label(__('admin.resources.product_category.is_active'))
                                    ->default(true),
                                Textarea::make('description_en')
                                    ->label(__('admin.resources.product_category.description_en'))
                                    ->columnSpanFull(),
                                Textarea::make('description_km')
                                    ->label(__('admin.resources.product_category.description_km'))
                                    ->columnSpanFull(),
                            ]),

                        Section::make(__('admin.resources.product_category.visuals'))
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('image_url')
                                    ->label(__('admin.resources.product_category.image'))
                                    ->image()
                                    ->directory('product-categories'),
                            ]),
                    ]),
            ]);
    }
}
