<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class VariantsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'variants';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('unit_id')
                    ->label(__('admin.resources.product.unit'))
                    ->relationship('unit', 'name')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('name')
                    ->label(__('admin.resources.unit.name'))
                    ->placeholder('e.g. 500g Pack, Bulk 5kg')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('quantity_in_unit')
                    ->label(__('admin.resources.product.quantity_in_unit'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric(),
                TextInput::make('price')
                    ->label(__('admin.resources.product.price'))
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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.unit.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.symbol')
                    ->label(__('admin.resources.product.unit'))
                    ->sortable(),
                TextColumn::make('quantity_in_unit')
                    ->label(__('admin.resources.product.quantity_in_unit'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('admin.resources.product.price'))
                    ->money('USD')
                    ->sortable(),
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
