<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('name_km')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.unit.name_km'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_en')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.unit.name_en'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('symbol')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.unit.symbol'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('conversion_to_base')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.unit.conversion'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                TextColumn::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
