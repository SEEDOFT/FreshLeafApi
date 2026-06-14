<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Tables;

use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendorInventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->modifyQueryUsing(fn ($query) => $query
                ->withExists('activeDiscount')
                ->orderByRaw('inventory_status_id = ? DESC', [VendorInventoryStatus::PENDING_REVIEW_ID])
                ->orderBy('active_discount_exists', 'desc')
                ->latest()
            )
            ->recordClasses(function (VendorInventory $record): ?string {
                $classes = [];
                if ((int) $record->inventory_status_id === VendorInventoryStatus::PENDING_REVIEW_ID) {
                    $classes[] = '!bg-danger-500/10';
                }
                if ($record->discount_percentage > 0) {
                    $classes[] = 'border-s-4 border-s-success-600 dark:border-s-success-400';
                }

                return ! empty($classes) ? implode(' ', $classes) : null;
            })
            ->columns([
                TextColumn::make('vendor.name')
                    ->label(__('admin.resources.vendor_inventory.vendor'))
                    ->getStateUsing(fn (VendorInventory $record) => $record->vendor->fullName)
                    ->searchable(['first_name', 'last_name']),
                ImageColumn::make('product.image_url')
                    ->label(__('admin.resources.product.image'))
                    ->getStateUsing(fn ($record) => resolve_image_url($record->product->image_url)),
                TextColumn::make('product.name_en')
                    ->label(__('admin.resources.product.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name_km')
                    ->label(__('admin.resources.product.name_km'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('admin.resources.product.unit_price'))
                    ->formatStateUsing(fn (VendorInventory $record): string => format_currency(
                        $record->price,
                        $record->currency->code
                    ))
                    ->description(function (VendorInventory $record): ?string {
                        if ($record->discount_percentage > 0) {
                            return __('shared.product.discount_label', [
                                'percentage' => format_number($record->discount_percentage, 0),
                                'price' => format_currency($record->discounted_price, $record->currency->code),
                            ]);
                        }

                        return null;
                    })
                    ->color(fn (VendorInventory $record) => $record->discount_percentage > 0 ? 'success' : null)
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('admin.resources.product.stock'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit.translated_name')
                    ->label(__('admin.resources.product.unit')),
                TextColumn::make('province_of_origin')
                    ->label(__('admin.resources.product.province_of_origin'))
                    ->searchable(),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.product.status'))
                    ->badge()
                    ->sortable(['name_en', 'name_km']),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendor_id')
                    ->label(__('admin.resources.vendor_inventory.vendor'))
                    ->relationship('vendor', 'last_name'),
                SelectFilter::make('product_category_id')
                    ->label(__('admin.resources.product.system_category'))
                    ->relationship('product.productCategory', 'name_en'),
                SelectFilter::make('inventory_status_id')
                    ->label(__('admin.resources.product.status'))
                    ->options(
                        VendorInventoryStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('approve')
                    ->label(__('admin.resources.vendor_inventory.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (VendorInventory $record): bool => (int) $record->inventory_status_id === VendorInventoryStatus::PENDING_REVIEW_ID)
                    ->action(function (VendorInventory $record): void {
                        $record->update([
                            'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
                        ]);

                        Notification::make()
                            ->title(__('admin.resources.vendor_inventory.notifications.approved'))
                            ->body(__('admin.resources.vendor_inventory.notifications.approved_body', [
                                'product' => translate($record->product->name_en, $record->product->name_km),
                            ]))
                            ->success()
                            ->sendToDatabase($record->vendor)
                            ->broadcast($record->vendor);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.resources.vendor_inventory.approve_heading'))
                    ->modalDescription(__('admin.resources.vendor_inventory.approve_description'))
                    ->modalSubmitActionLabel(__('admin.resources.vendor_inventory.approve_submit')),
            ]);
    }
}
