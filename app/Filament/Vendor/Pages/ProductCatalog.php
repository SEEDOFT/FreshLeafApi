<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Filament\Vendor\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\Unit;
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
use Illuminate\Support\HtmlString;
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
            ->query(Product::query()->where('product_status_id', ProductStatus::PUBLISHED_ID))
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('image_url')
                        ->imageSize('200')
                        ->width('100%'),
                    TextColumn::make('name_en')->placeholder(__('admin.resources.general.not_provided'))
                        ->weight('bold')
                        ->size('lg')
                        ->searchable(),
                    TextColumn::make('name_km')->placeholder(__('admin.resources.general.not_provided'))
                        ->size('md')
                        ->color('gray'),
                    TextColumn::make('productCategory.name_en')->placeholder(__('admin.resources.general.not_provided'))
                        ->badge()
                        ->color('info'),
                ]),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(new HtmlString('<strong>'.__('shared.product.system_category').'</strong>'))
                    ->relationship('productCategory', 'name_en'),
            ])
            ->actions([
                Action::make('view')
                    ->label(new HtmlString('<strong>'.__('shared.product.view_detail').'</strong>'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->infolist(fn (Schema $infolist): Schema => $infolist
                        ->schema([
                            Section::make(__('shared.product.general_info'))
                                ->columns(2)
                                ->schema([
                                    ImageEntry::make('image_url')
                                        ->label(new HtmlString('<strong>'.__('shared.product.image').'</strong>'))
                                        ->columnSpanFull()
                                        ->circular(),
                                    TextEntry::make('name_en')->placeholder(__('admin.resources.general.not_provided'))
                                        ->label(new HtmlString('<strong>'.__('shared.product.name_en').'</strong>'))
                                        ->weight('bold'),
                                    TextEntry::make('name_km')->placeholder(__('admin.resources.general.not_provided'))
                                        ->label(new HtmlString('<strong>'.__('shared.product.name_km').'</strong>')),
                                    TextEntry::make('productCategory.name_en')->placeholder(__('admin.resources.general.not_provided'))
                                        ->label(new HtmlString('<strong>'.__('shared.product.system_category').'</strong>'))
                                        ->badge()
                                        ->color('info'),
                                    TextEntry::make('defaultUnit.name')->placeholder(__('admin.resources.general.not_provided'))
                                        ->label(new HtmlString('<strong>'.__('shared.product.default_unit').'</strong>')),
                                    TextEntry::make('description_en')->placeholder(__('admin.resources.general.not_provided'))
                                        ->label(new HtmlString('<strong>'.__('shared.product.description_en').'</strong>'))
                                        ->columnSpanFull()
                                        ->placeholder('-'),
                                    TextEntry::make('description_km')->placeholder(__('admin.resources.general.not_provided'))
                                        ->label(new HtmlString('<strong>'.__('shared.product.description_km').'</strong>'))
                                        ->columnSpanFull()
                                        ->placeholder('-'),
                                    KeyValueEntry::make('nutrition_data')
                                        ->label(new HtmlString('<strong>'.__('shared.product.nutrition_data').'</strong>'))
                                        ->columnSpanFull()
                                        ->visible(fn ($record) => ! empty($record->nutrition_data)),
                                ]),
                        ])),
                Action::make('addToStore')
                    ->label(new HtmlString('<strong>'.__('shared.product.add_to_store').'</strong>'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('price')
                            ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label(new HtmlString('<strong>'.__('shared.product.quantity').'</strong>'))
                            ->numeric()
                            ->required(),
                        Select::make('unit_id')
                            ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>'))
                            ->options(Unit::all()->pluck('translated_name', 'id'))
                            ->required(),
                        TextInput::make('province_of_origin')
                            ->label(new HtmlString('<strong>'.__('shared.product.province_of_origin').'</strong>')),
                        TextInput::make('certification_type')
                            ->label(new HtmlString('<strong>'.__('shared.product.certification_type').'</strong>')),
                        TextInput::make('farm_location')
                            ->label(new HtmlString('<strong>'.__('shared.product.farm_location').'</strong>')),
                        DatePicker::make('harvest_date')
                            ->label(new HtmlString('<strong>'.__('shared.product.harvest_date').'</strong>')),
                        TextInput::make('shelf_life_days')
                            ->label(new HtmlString('<strong>'.__('shared.product.shelf_life').'</strong>'))
                            ->numeric()
                            ->suffix(' '.__('shared.product.days')),
                        TextInput::make('packaging_type')
                            ->label(new HtmlString('<strong>'.__('shared.product.packaging_type').'</strong>')),
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
