<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Tables;

use App\Constants\StorageDirectory;
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
use Illuminate\Support\HtmlString;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                ImageColumn::make('product.image_url')
                    ->label(new HtmlString('<strong>'.__('shared.product.image').'</strong>')),

                TextColumn::make('product.name_en')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.name_en').'</strong>'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name_km')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.name_km').'</strong>'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.unit_price').'</strong>'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('stock_quantity')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.stock').'</strong>'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit.name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.unit').'</strong>')),

                TextColumn::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.product.status').'</strong>'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(new HtmlString('<strong>'.__('shared.updated_at').'</strong>'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(new HtmlString('<strong>'.__('shared.product.system_category').'</strong>'))
                    ->relationship('product.productCategory', 'name_en'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('adjustStock')
                    ->label(new HtmlString('<strong>'.__('shared.product.adjust_stock').'</strong>'))
                    ->icon('heroicon-o-adjustments-vertical')
                    ->color('warning')
                    ->form([
                        FormSelect::make('type')
                            ->label(new HtmlString('<strong>'.__('shared.product.adjustment_type').'</strong>'))
                            ->options([
                                'IN' => 'Restock (In)',
                                'OUT' => 'Sold / Removed (Out)',
                                'LOSS' => 'Damage / Loss',
                                'CORRECTION' => 'Correction',
                            ])
                            ->required()
                            ->reactive(),
                        FormTextInput::make('quantity_change')
                            ->label(new HtmlString('<strong>'.__('shared.product.quantity_change').'</strong>'))
                            ->helperText('Use negative numbers for stock reduction.')
                            ->numeric()
                            ->required(),
                        FileUpload::make('proof_image_path')
                            ->label(new HtmlString('<strong>'.__('shared.product.proof_photo').'</strong>'))
                            ->image()
                            ->directory(StorageDirectory::INVENTORY_ADJUSTMENTS)
                            ->visibility('public')
                            ->required(fn ($get) => in_array($get('type'), ['IN', 'LOSS'])),
                        Textarea::make('notes')
                            ->label(new HtmlString('<strong>'.__('shared.product.reason').'</strong>'))
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
