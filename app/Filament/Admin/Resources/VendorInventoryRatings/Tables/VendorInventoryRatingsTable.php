<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventoryRatings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VendorInventoryRatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.fullName')
                    ->label(__('admin.resources.rating.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vendorInventory.product.name_en')
                    ->label(__('admin.resources.rating.product'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label(__('admin.resources.rating.rating'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->sortable(),

                TextColumn::make('review')
                    ->label(__('admin.resources.rating.review'))
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('shared.rating.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}
