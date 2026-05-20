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
use Illuminate\Support\HtmlString;
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
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit').'</strong>'))
                    ->relationship('unit', 'name')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('name')
                    ->label(new HtmlString('<strong>'.__('admin.resources.unit.name').'</strong>'))
                    ->placeholder('e.g. 500g Pack, Bulk 5kg')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('quantity_in_unit')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.quantity_in_unit').'</strong>'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric(),
                TextInput::make('price')
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.price').'</strong>'))
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
                TextColumn::make('name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.unit.name').'</strong>'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.symbol')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.unit').'</strong>'))
                    ->sortable(),
                TextColumn::make('quantity_in_unit')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.quantity_in_unit').'</strong>'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('admin.resources.product.price').'</strong>'))
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
