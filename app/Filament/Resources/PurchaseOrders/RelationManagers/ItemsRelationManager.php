<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('product_variant_id')
                    ->relationship('productVariant', 'name', fn ($query, $get) => $query->where('product_id', $get('product_id')))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('qty_ordered')
                    ->required()
                    ->numeric(),
                TextInput::make('cost_per_unit')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('batch_code')
                    ->placeholder('e.g. B-2024-001'),
                DatePicker::make('expiry_date'),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_code')
            ->columns([
                TextColumn::make('product.name')
                    ->sortable(),
                TextColumn::make('productVariant.name')
                    ->sortable(),
                TextColumn::make('qty_ordered')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost_per_unit')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('batch_code')
                    ->searchable(),
                TextColumn::make('expiry_date')
                    ->date()
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
