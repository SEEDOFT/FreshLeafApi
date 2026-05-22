<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                Select::make('product_id')
                    ->label(__('admin.resources.order.product'))
                    ->relationship('product', 'name')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->live(),
                Select::make('product_variant_id')
                    ->label(__('admin.resources.product.variant'))
                    ->relationship('productVariant', 'name',
                        fn (Builder $query, Get $get) => $query->where('product_id', $get('product_id'))
                    )
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('quantity')
                    ->label(__('admin.resources.product.quantity'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric(),
                TextInput::make('unit_price_snapshot')
                    ->label(__('admin.resources.product.unit_price'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('subtotal')
                    ->label(__('admin.resources.order.subtotal'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
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
                    ->label(__('admin.resources.order.product'))
                    ->sortable(),
                TextColumn::make('unit_snapshot')
                    ->label(__('admin.resources.product.unit')),
                TextColumn::make('unit_price_snapshot')
                    ->money('USD')
                    ->label(__('admin.resources.product.unit_price')),
                TextColumn::make('quantity')
                    ->label(__('admin.resources.product.quantity'))
                    ->numeric(),
                TextColumn::make('subtotal')
                    ->label(__('admin.resources.order.subtotal'))
                    ->money('USD'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
