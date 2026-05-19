<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Filament\Vendor\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Override;

class ProductCatalog extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    #[Override]
    protected string $view = 'filament.vendor.pages.product-catalog';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    public function getTitle(): string
    {
        return __('shared.product.add_product');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->where('product_status_id', ProductStatus::ACTIVE))
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('image_url')
                        ->imageSize('200')
                        ->width('100%'),
                    TextColumn::make('name_en')
                        ->weight('bold')
                        ->size('lg')
                        ->searchable(),
                    TextColumn::make('name_km')
                        ->size('md')
                        ->color('gray'),
                    TextColumn::make('productCategory.name_en')
                        ->badge()
                        ->color('info'),
                ]),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('shared.product.system_category'))
                    ->relationship('productCategory', 'name_en'),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('shared.product.view_detail'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->infolist(fn (Schema $infolist): Schema => $infolist
                        ->schema([
                            Section::make(__('shared.product.general_info'))
                                ->columns(2)
                                ->schema([
                                    ImageEntry::make('image_url')
                                        ->label(__('shared.product.image'))
                                        ->columnSpanFull()
                                        ->circular(),
                                    TextEntry::make('name_en')
                                        ->label(__('shared.product.name_en'))
                                        ->weight('bold'),
                                    TextEntry::make('name_km')
                                        ->label(__('shared.product.name_km')),
                                    TextEntry::make('productCategory.name_en')
                                        ->label(__('shared.product.system_category'))
                                        ->badge()
                                        ->color('info'),
                                    TextEntry::make('defaultUnit.name')
                                        ->label(__('shared.product.default_unit')),
                                    TextEntry::make('description_en')
                                        ->label(__('shared.product.description_en'))
                                        ->columnSpanFull()
                                        ->placeholder('-'),
                                    TextEntry::make('description_km')
                                        ->label(__('shared.product.description_km'))
                                        ->columnSpanFull()
                                        ->placeholder('-'),
                                    KeyValueEntry::make('nutrition_data')
                                        ->label(__('shared.product.nutrition_data'))
                                        ->columnSpanFull()
                                        ->visible(fn ($record) => ! empty($record->nutrition_data)),
                                ]),
                        ])),
                Action::make('addToStore')
                    ->label(__('shared.product.add_to_store'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('price')
                            ->label(__('shared.product.unit_price'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(__('shared.product.quantity'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(__('shared.product.unit'))
                            ->relationship('defaultUnit', 'name')
                            ->required(),
                        TextInput::make('province_of_origin')
                            ->label(__('shared.product.province_of_origin')),
                        TextInput::make('certification_type')
                            ->label(__('shared.product.certification_type')),
                        TextInput::make('farm_location')
                            ->label(__('shared.product.farm_location')),
                        DatePicker::make('harvest_date')
                            ->label(__('shared.product.harvest_date')),
                        TextInput::make('shelf_life_days')
                            ->label(__('shared.product.shelf_life'))
                            ->numeric()
                            ->suffix(' '.__('shared.product.days')),
                        TextInput::make('packaging_type')
                            ->label(__('shared.product.packaging_type')),
                    ])
                    ->action(function (Product $record, array $data): void {
                        VendorInventory::create([
                            'vendor_id' => auth()->id(),
                            'product_id' => $record->id,
                            'price' => $data['price'],
                            'stock_quantity' => $data['stock_quantity'],
                            'unit_id' => $data['unit_id'],
                            'province_of_origin' => $data['province_of_origin'] ?? null,
                            'certification_type' => $data['certification_type'] ?? null,
                            'farm_location' => $data['farm_location'] ?? null,
                            'harvest_date' => $data['harvest_date'] ?? null,
                            'shelf_life_days' => $data['shelf_life_days'] ?? null,
                            'packaging_type' => $data['packaging_type'] ?? null,
                            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('shared.product.notifications.added_to_store'))
                            ->send();

                        $this->redirect(ProductResource::getUrl(panel: 'vendor'));
                    }),
            ]);
    }
}
