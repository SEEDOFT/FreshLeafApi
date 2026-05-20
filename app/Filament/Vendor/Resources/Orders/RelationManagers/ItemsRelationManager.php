<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                TextInput::make('product_name_snapshot')
                    ->label(new HtmlString('<strong>'.__('shared.order.product').'</strong>'))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('unit_snapshot')
                    ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>'))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('unit_price_snapshot')
                    ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('quantity')
                    ->label(new HtmlString('<strong>'.__('shared.product.quantity').'</strong>'))
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric(),
                TextInput::make('subtotal')
                    ->label(new HtmlString('<strong>'.__('shared.order.subtotal').'</strong>'))
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
                TextColumn::make('product_name_snapshot')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.order.product').'</strong>'))
                    ->sortable(),
                TextColumn::make('unit_snapshot')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>')),
                TextColumn::make('unit_price_snapshot')->placeholder(__('admin.resources.general.not_provided'))
                    ->money('USD')
                    ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>')),
                TextColumn::make('quantity')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.quantity').'</strong>'))
                    ->numeric(),
                TextColumn::make('subtotal')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.order.subtotal').'</strong>'))
                    ->money('USD'),
                TextColumn::make('commission_amount')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.order.commission').'</strong>'))
                    ->money('USD'),
                TextColumn::make('vendor_net_amount')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.order.total').'</strong>'))
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
