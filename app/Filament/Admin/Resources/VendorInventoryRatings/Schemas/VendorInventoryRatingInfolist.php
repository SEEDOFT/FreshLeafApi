<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventoryRatings\Schemas;

use App\Filament\Admin\Resources\VendorInventories\VendorInventoryResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorInventoryRatingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.rating.user_info'))
                    ->relationship('user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('fullName')
                            ->label(__('admin.resources.user.full_name')),
                        TextEntry::make('email')
                            ->label(__('admin.resources.user.email')),
                        TextEntry::make('phone_number')
                            ->label(__('admin.resources.user.phone')),
                    ]),

                Section::make(__('admin.resources.rating.product_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendorInventory.product.name_en')
                            ->label(__('admin.resources.product.name_en'))
                            ->url(fn ($record) => VendorInventoryResource::getUrl('view', ['record' => $record->vendor_inventory_id]))
                            ->color('primary'),
                        TextEntry::make('vendorInventory.product.name_km')
                            ->label(__('admin.resources.product.name_km') ?? 'Product Name (KM)'),
                        TextEntry::make('vendorInventory.unit.translated_name')
                            ->label(__('admin.resources.product.unit')),
                        TextEntry::make('vendorInventory.price')
                            ->label(__('admin.resources.product.unit_price'))
                            ->formatStateUsing(fn ($record) => format_currency($record->vendorInventory->price, $record->vendorInventory->currency->code)),
                    ]),

                Section::make(__('admin.resources.rating.rating_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rating')
                            ->label(__('admin.resources.rating.rating'))
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state >= 4 => 'success',
                                $state >= 3 => 'warning',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state)),
                        TextEntry::make('review')
                            ->label(__('admin.resources.rating.review'))
                            ->columnSpanFull(),
                        TextEntry::make('orderItem.order.order_number')
                            ->label(__('admin.resources.rating.order_code')),
                        TextEntry::make('orderItem.id')
                            ->label(__('admin.resources.rating.order_item')),
                    ]),

                Section::make(__('admin.resources.rating.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
