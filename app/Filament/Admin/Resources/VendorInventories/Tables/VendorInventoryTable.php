<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Tables;

use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class VendorInventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('vendor.name')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor_inventory.vendor').'</strong>'))
                    ->getStateUsing(
                        static fn (VendorInventory $record) => $record->vendor->fullName
                    )
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                ImageColumn::make('product.image_url')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.image').'</strong>')),
                TextColumn::make('product.name_en')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.name_en').'</strong>'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit_price').'</strong>'))
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.stock').'</strong>'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit.name')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit').'</strong>')),
                TextColumn::make('province_of_origin')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.province_of_origin').'</strong>'))
                    ->searchable(),
                TextColumn::make('status.translated_name')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.status').'</strong>'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendor_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor_inventory.vendor').'</strong>'))
                    ->relationship('vendor', 'last_name'),
                SelectFilter::make('product_category_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.system_category').'</strong>'))
                    ->relationship('product.productCategory', 'name_en'),
                SelectFilter::make('inventory_status_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.status').'</strong>'))
                    ->options(
                        VendorInventoryStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
