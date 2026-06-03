<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Filament\Vendor\Pages\Schemas\AddToStoreForm;
use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
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
        return __('shared.form.add_product');
    }

    public function table(Table $table): Table
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $table
            ->query(Product::query()->where('product_status_id', ProductStatus::PUBLISHED_ID))
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([12, 24, 48, 'all'])
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
                        ->placeholder($notProvided)
                        ->weight('bold')
                        ->size('lg')
                        ->searchable(),
                    TextColumn::make('name_km')
                        ->placeholder($notProvided)
                        ->size('md')
                        ->color('gray'),
                    TextColumn::make('productCategory.translated_name')
                        ->placeholder($notProvided)
                        ->badge()
                        ->color('info'),
                ]),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('shared.product.system_category'))
                    ->options(
                        ProductCategory::all()->pluck('translated_name', 'id')
                    ),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('shared.product.view_detail'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalSubmitAction(false)
                    ->infolist([
                        TextEntry::make('image_url')
                            ->label(__('shared.product.image'))
                            ->columnSpanFull(),
                        TextEntry::make('name_en')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.name_en')),
                        TextEntry::make('name_km')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.name_km')),
                        TextEntry::make('productCategory.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.system_category'))
                            ->badge()
                            ->color('info'),
                        TextEntry::make('defaultUnit.translated_name')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.default_unit')),
                        TextEntry::make('description_en')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.description_en'))
                            ->columnSpanFull(),
                        TextEntry::make('description_km')
                            ->placeholder($notProvided)
                            ->label(__('shared.product.description_km'))
                            ->columnSpanFull(),
                        TextEntry::make('nutrition_data')
                            ->label(__('shared.product.nutrition_data'))
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record->nutrition_data))
                            ->formatStateUsing(fn ($state): string => is_array($state)
                                ? collect($state)
                                    ->map(fn ($value, $key): string => is_array($value) ? "$key: ".json_encode($value) : "$key: $value")
                                    ->implode("\n")
                                : (string) $state),
                    ]),
                Action::make('addToStore')
                    ->label(__('shared.product.add_to_store'))
                    ->icon('heroicon-o-plus')
                    ->form(AddToStoreForm::schema())
                    ->visible(fn (Product $record): bool => ! VendorInventory::where('vendor_id', Auth::id())->where('product_id', $record->id)->exists())
                    ->action(function (Product $record, array $data): void {
                        if (VendorInventory::where('vendor_id', Auth::id())->where('product_id', $record->id)->exists()) {
                            Notification::make()
                                ->danger()
                                ->title(__('shared.product.notifications.already_in_store'))
                                ->send();

                            return;
                        }

                        VendorInventory::create([
                            'vendor_id' => Auth::id(),
                            'product_id' => $record->id,
                            'currency_id' => $data['currency_id'],
                            'price' => $data['price'],
                            'stock_quantity' => $data['stock_quantity'],
                            'unit_id' => $data['unit_id'],
                            'province_of_origin' => $data['province_of_origin'],
                            'certification_type' => $data['certification_type'],
                            'farm_location' => $data['farm_location'],
                            'harvest_date' => $data['harvest_date'],
                            'shelf_life_days' => $data['shelf_life_days'],
                            'packaging_type_id' => $data['packaging_type_id'],
                            'batch_images' => $data['batch_images'],
                            'inventory_status_id' => VendorInventoryStatus::PENDING_REVIEW_ID,
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('shared.product.notifications.added_to_store'))
                            ->send();

                        $this->redirect(ProductInventoryResource::getUrl(panel: 'vendor'));
                    }),
            ]);
    }
}
