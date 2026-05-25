<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\RelationManagers;

use App\Models\VendorInventoryDiscountHistory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class DiscountsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'discounts';

    #[Override]
    protected static ?string $recordTitleAttribute = 'discount_percentage';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('discount_percentage')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                DateTimePicker::make('starts_at')
                    ->label('Starts At (Optional)'),
                DateTimePicker::make('ends_at')
                    ->label('Ends At (Optional)'),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('discount_percentage')
            ->columns([
                TextColumn::make('discount_percentage')
                    ->formatStateUsing(fn ($state) => (float) $state.'%'),
                TextColumn::make('starts_at')
                    ->dateTime(),
                TextColumn::make('ends_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->after(function ($record) {
                        VendorInventoryDiscountHistory::create([
                            'vendor_inventory_discount_id' => $record->id,
                            'vendor_inventory_id' => $record->vendor_inventory_id,
                            'discount_percentage' => $record->discount_percentage,
                            'starts_at' => $record->starts_at,
                            'ends_at' => $record->ends_at,
                            'action_type' => 'created',
                        ]);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->after(function ($record) {
                        VendorInventoryDiscountHistory::create([
                            'vendor_inventory_discount_id' => $record->id,
                            'vendor_inventory_id' => $record->vendor_inventory_id,
                            'discount_percentage' => $record->discount_percentage,
                            'starts_at' => $record->starts_at,
                            'ends_at' => $record->ends_at,
                            'action_type' => 'updated',
                        ]);
                    }),
                DeleteAction::make()
                    ->before(function ($record) {
                        VendorInventoryDiscountHistory::create([
                            'vendor_inventory_discount_id' => $record->id,
                            'vendor_inventory_id' => $record->vendor_inventory_id,
                            'discount_percentage' => $record->discount_percentage,
                            'starts_at' => $record->starts_at,
                            'ends_at' => $record->ends_at,
                            'action_type' => 'deleted',
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                VendorInventoryDiscountHistory::create([
                                    'vendor_inventory_discount_id' => $record->id,
                                    'vendor_inventory_id' => $record->vendor_inventory_id,
                                    'discount_percentage' => $record->discount_percentage,
                                    'starts_at' => $record->starts_at,
                                    'ends_at' => $record->ends_at,
                                    'action_type' => 'deleted',
                                ]);
                            }
                        }),
                ]),
            ]);
    }
}
