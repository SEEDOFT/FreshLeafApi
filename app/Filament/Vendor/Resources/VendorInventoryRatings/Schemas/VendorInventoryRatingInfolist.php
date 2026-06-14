<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorInventoryRatings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorInventoryRatingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.rating.user_info'))
                    ->relationship('user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('fullName')
                            ->label(__('shared.user.full_name')),
                        TextEntry::make('email')
                            ->label(__('shared.user.email')),
                    ]),

                Section::make(__('shared.rating.rating_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rating')
                            ->label(__('shared.rating.rating'))
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state >= 4 => 'success',
                                $state >= 3 => 'warning',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state)),
                        TextEntry::make('review')
                            ->label(__('shared.rating.review'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.rating.product_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendorInventory.product.name_en')
                            ->label(__('shared.product.name_en')),
                        TextEntry::make('vendorInventory.product.name_km')
                            ->label(__('shared.product.name_km')),
                        TextEntry::make('vendorInventory.unit.translated_name')
                            ->label(__('shared.product.unit')),
                        TextEntry::make('vendorInventory.price')
                            ->label(__('shared.product.unit_price'))
                            ->money(fn ($record) => $record->vendorInventory->currency->code),
                    ]),

                Section::make(__('shared.rating.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('shared.rating.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('shared.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
