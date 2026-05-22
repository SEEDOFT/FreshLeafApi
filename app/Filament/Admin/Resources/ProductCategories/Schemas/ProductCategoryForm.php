<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductCategories\Schemas;

use App\Constants\StorageDirectory;
use App\Models\ProductCategoryStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        // Left Column
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('admin.resources.product_category.basic_info'))
                                    ->schema([
                                        TextInput::make('name_en')
                                            ->label(__('admin.resources.product_category.name_en'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(static function (Get $get, Set $set, ?string $state): void {
                                                if (! $get('slug')) {
                                                    $set('slug', Str::slug($state ?? ''));
                                                }
                                            }),
                                        TextInput::make('name_km')
                                            ->label(__('admin.resources.product_category.name_km'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                                        TextInput::make('slug')
                                            ->label(__('admin.resources.product_category.slug'))
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->unique(ignoreRecord: true),
                                        Select::make('product_category_status_id')
                                            ->label(__('admin.resources.product_category.status'))
                                            ->options(
                                                ProductCategoryStatus::all()
                                                    ->pluck('translated_name', 'id')
                                            )
                                            ->required()
                                            ->default(ProductCategoryStatus::ACTIVE_ID),
                                        Textarea::make('description_en')
                                            ->label(__('admin.resources.product_category.description_en'))
                                            ->columnSpanFull(),
                                        Textarea::make('description_km')
                                            ->label(__('admin.resources.product_category.description_km'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Right Column
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('admin.resources.product_category.visuals'))
                                    ->schema([
                                        FileUpload::make('image_url')
                                            ->label(__('admin.resources.product_category.image'))
                                            ->image()
                                            ->disk('public')
                                            ->directory(StorageDirectory::PRODUCT_CATEGORIES),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
