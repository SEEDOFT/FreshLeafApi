<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class ItemsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'items';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name_snapshot')
                    ->label(__('shared.order.product'))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('unit_snapshot')
                    ->label(__('shared.product.unit'))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('unit_price_snapshot')
                    ->label(__('shared.product.unit_price'))
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('quantity')
                    ->label(__('shared.product.quantity'))
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric(),
                TextInput::make('subtotal')
                    ->label(__('shared.order.subtotal'))
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->prefix('$'),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordTitleAttribute('product_name_snapshot')
            ->columns([
                TextColumn::make('product_name_snapshot')
                    ->label(__('shared.order.product'))
                    ->sortable(),
                TextColumn::make('unit_snapshot')
                    ->label(__('shared.product.unit')),
                TextColumn::make('unit_price_snapshot')
                    ->money('USD')
                    ->label(__('shared.product.unit_price')),
                TextColumn::make('quantity')
                    ->label(__('shared.product.quantity'))
                    ->numeric(),
                TextColumn::make('subtotal')
                    ->label(__('shared.order.subtotal'))
                    ->money('USD'),
                TextColumn::make('commission_amount')
                    ->label(__('shared.order.commission'))
                    ->money('USD'),
                TextColumn::make('vendor_net_amount')
                    ->label(__('shared.order.total'))
                    ->money('USD'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
