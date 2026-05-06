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
use Illuminate\Support\HtmlString;
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
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.product').'</strong>'))
                    ->relationship('product', 'name')
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->live(),
                Select::make('product_variant_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.variant').'</strong>'))
                    ->relationship('productVariant', 'name',
                        static fn (Builder $query, Get $get) => $query->where('product_id', $get('product_id'))
                    )
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('quantity')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.quantity').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->numeric(),
                TextInput::make('unit_price_snapshot')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit_price').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('subtotal')
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.subtotal').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
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
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.product').'</strong>'))
                    ->sortable(),
                TextColumn::make('unit_snapshot')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit').'</strong>')),
                TextColumn::make('unit_price_snapshot')
                    ->money('USD')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit_price').'</strong>')),
                TextColumn::make('quantity')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.quantity').'</strong>'))
                    ->numeric(),
                TextColumn::make('subtotal')
                    ->label(new HtmlString('<strong>'.__('admin.resources.order.subtotal').'</strong>'))
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
