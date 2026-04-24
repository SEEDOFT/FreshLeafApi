<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryBatches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('batch_code')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('inventory_batch_status_id')
                            ->relationship('status', 'name')
                            ->required(),
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Select::make('product_variant_id')
                            ->relationship('productVariant', 'name', static fn ($query, $get) => $query->where('product_id', $get('product_id')))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('received_at')
                            ->default(now()),
                        DatePicker::make('expiry_date'),
                        TextInput::make('cost_per_unit')
                            ->numeric()
                            ->prefix('$'),
                    ]),

                Section::make('Quantities')
                    ->columns(3)
                    ->schema([
                        TextInput::make('received_qty')
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('sold_qty')
                            ->numeric()
                            ->default(0),
                        TextInput::make('damaged_qty')
                            ->numeric()
                            ->default(0),
                        TextInput::make('expired_qty')
                            ->numeric()
                            ->default(0),
                        TextInput::make('reserved_qty')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
