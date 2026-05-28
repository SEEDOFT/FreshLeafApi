<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Tables;

use App\Filament\Vendor\Resources\ProductInventories\Schemas\AdjustStockForm;
use App\Models\ProductCategory;
use App\Models\VendorInventory;
use App\Models\VendorInventoryDiscountHistory;
use App\Models\VendorInventoryStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductInventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('product.image_url')
                    ->label(__('shared.product.image')),

                TextColumn::make('product.name_en')
                    ->label(__('shared.product.name_en'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name_km')
                    ->label(__('shared.product.name_km'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('shared.product.unit_price'))
                    ->money(fn (VendorInventory $record) => $record->currency->code)
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label(__('shared.product.stock'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit.translated_name')
                    ->label(__('shared.product.unit')),

                TextColumn::make('status.translated_name')
                    ->label(__('shared.product.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('shared.updated_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('shared.product.system_category'))
                    ->options(
                        ProductCategory::all()
                            ->pluck('translated_name', 'id')
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('addDiscount')
                    ->label(__('shared.product.add_discount'))
                    ->icon('heroicon-o-receipt-percent')
                    ->color('info')
                    ->form([
                        TextInput::make('discount_percentage')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        DateTimePicker::make('starts_at')
                            ->label(__('shared.product.discount_starts_at')),
                        DateTimePicker::make('ends_at')
                            ->label(__('shared.product.discount_ends_at')),
                    ])
                    ->action(function (VendorInventory $record, array $data): void {
                        $discount = $record->discounts()->create([
                            'discount_percentage' => $data['discount_percentage'],
                            'starts_at' => $data['starts_at'],
                            'ends_at' => $data['ends_at'],
                        ]);

                        VendorInventoryDiscountHistory::create([
                            'vendor_inventory_discount_id' => $discount->id,
                            'vendor_inventory_id' => $record->id,
                            'discount_percentage' => $discount->discount_percentage,
                            'starts_at' => $discount->starts_at,
                            'ends_at' => $discount->ends_at,
                            'action_type' => 'created',
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('shared.product.notifications.discount_added'))
                            ->send();
                    }),
                Action::make('adjustStock')
                    ->label(__('shared.product.adjust_stock'))
                    ->icon('heroicon-o-adjustments-vertical')
                    ->color('warning')
                    ->form(AdjustStockForm::schema())
                    ->action(function (VendorInventory $record, array $data): void {
                        $record->adjustStock(
                            change: (float) $data['quantity_change'],
                            type: $data['type'],
                            reason: $data['notes'],
                            proofImagePath: $data['proof_image_path'] ?? null,
                            notes: $data['notes'],
                        );

                        $record->update([
                            'inventory_status_id' => VendorInventoryStatus::PENDING_REVIEW_ID,
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('shared.product.notifications.stock_adjusted'))
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
