<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\ProductCategory;
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
                Section::make(__('admin.resources.product.general_info'))
                    ->columns(2)
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

                Grid::make(3)
                    ->schema([
                        Section::make(__('admin.resources.product.categorization'))
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                Select::make('product_category_id')
                                    ->label(__('admin.resources.product.system_category'))
                                    ->relationship('productCategory', 'name_en')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->helperText(function ($state) {
                                        $helpers = __('admin.resources.product.category_helpers');
                                        if (! $state) {
                                            return null;
                                        }
                                        $cat = ProductCategory::find($state);
                                        if (! $cat instanceof ProductCategory) {
                                            return null;
                                        }
                                        if ($cat->id === ProductCategory::LEAFY) {
                                            return $helpers['leafy'];
                                        }
                                        if ($cat->id === ProductCategory::FRUIT) {
                                            return $helpers['fruit'];
                                        }
                                        if ($cat->id === ProductCategory::ROOT_AND_TUBER) {
                                            return $helpers['root'];
                                        }
                                        if ($cat->id === ProductCategory::BULB_AND_STEM) {
                                            return $helpers['bulb'];
                                        }
                                        if ($cat->id === ProductCategory::LEGUME) {
                                            return $helpers['legume'];
                                        }
                                        if ($cat->id === ProductCategory::INDIGENOUS_AND_WILD) {
                                            return $helpers['indigenous'];
                                        }

                                        return null;
                                    }),
                                Select::make('product_type_id')
                                    ->label(__('admin.resources.product.type'))
                                    ->relationship('type', 'name')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                Select::make('product_status_id')
                                    ->label(__('admin.resources.product.status'))
                                    ->relationship('status', 'name')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                Select::make('default_unit_id')
                                    ->label(__('admin.resources.product.unit'))
                                    ->relationship('defaultUnit', 'name')
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
