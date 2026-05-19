<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('product.image_url')
                    ->label(__('shared.product.image')),

                TextColumn::make('product.name_en')
                    ->label(__('shared.product.name_en'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name_km')
                    ->label(__('shared.product.name_km'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('shared.product.unit_price'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label(__('shared.product.stock'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit.name')
                    ->label(__('shared.product.unit')),

                TextColumn::make('status.name')
                    ->label(__('shared.product.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('shared.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('shared.product.system_category'))
                    ->relationship('product.productCategory', 'name_en'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('adjustStock')
                    ->label(__('shared.product.adjust_stock'))
                    ->icon('heroicon-o-adjustments-vertical')
                    ->color('warning')
                    ->form([
                        FormSelect::make('type')
                            ->label(__('shared.product.adjustment_type'))
                            ->options([
                                'IN' => 'Restock (In)',
                                'OUT' => 'Sold / Removed (Out)',
                                'LOSS' => 'Damage / Loss',
                                'CORRECTION' => 'Correction',
                            ])
                            ->required()
                            ->reactive(),
                        FormTextInput::make('quantity_change')
                            ->label(__('shared.product.quantity_change'))
                            ->helperText('Use negative numbers for stock reduction.')
                            ->numeric()
                            ->required(),
                        FileUpload::make('proof_image_path')
                            ->label(__('shared.product.proof_photo'))
                            ->image()
                            ->directory('inventory-proofs')
                            ->visibility('public')
                            ->required(fn ($get) => in_array($get('type'), ['IN', 'LOSS'])),
                        Textarea::make('notes')
                            ->label(__('shared.product.reason'))
                            ->placeholder('Explain why you are adjusting the stock...')
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->adjustStock(
                            change: (float) $data['quantity_change'],
                            type: $data['type'],
                            reason: $data['notes'],
                            proofImagePath: $data['proof_image_path'] ?? null,
                            notes: $data['notes'],
                        );

                        Notification::make()
                            ->success()
                            ->title(__('shared.product.notifications.stock_adjusted'))
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
