<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Constants\StorageDirectory;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make(__('admin.resources.product.general_info'))
                            ->columns(1)
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('name_en')
                                    ->label(__('admin.resources.product.name_en'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                                TextInput::make('name_km')
                                    ->label(__('admin.resources.product.name_km'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TextInput::make('slug')
                                    ->label(__('admin.resources.product.slug'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->unique(ignoreRecord: true),
                                Textarea::make('description_en')
                                    ->label(__('admin.resources.product.description_en'))
                                    ->columnSpanFull(),
                                Textarea::make('description_km')
                                    ->label(__('admin.resources.product.description_km'))
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make()
                    ->columnSpan(1)
                    ->extraAttributes(['style' => 'height: 100%; display: flex; flex-direction: column; justify-content: stretch;'])
                    ->schema([
                        FileUpload::make('image_url')
                            ->label(__('admin.resources.product.image'))
                            ->disk('public')
                            ->image()
                            ->directory(StorageDirectory::PRODUCTS)
                            ->extraAttributes([
                                'class' => 'custom-file-upload-height',
                                'style' => 'flex-grow: 1; width: 100%; display: flex; flex-direction: column; justify-content: stretch; align-items: stretch;',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),
                Grid::make(2)
                    ->schema([
                        Section::make(__('admin.resources.product.categorization'))
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                Select::make('product_category_id')
                                    ->label(__('admin.resources.product.system_category'))
                                    ->options(
                                        ProductCategory::all()
                                            ->pluck('translated_name', 'id')
                                    )
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                Select::make('product_status_id')
                                    ->label(__('admin.resources.product.status'))
                                    ->options(
                                        ProductStatus::all()
                                            ->pluck('translated_name', 'id')
                                    )
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                            ]),
                    ]),
                Section::make(__('admin.resources.product.nutrition_data'))
                    ->schema([
                        KeyValue::make('nutrition_data')
                            ->label(__('admin.resources.product.nutrition_data'))
                            ->addActionLabel(__('admin.resources.product.add_nutrition'))
                            ->keyLabel(__('admin.resources.product.nutrition_key'))
                            ->valueLabel(__('admin.resources.product.nutrition_value')),
                    ]),
            ]);
    }
}
