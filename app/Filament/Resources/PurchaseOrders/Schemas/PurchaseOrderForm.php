<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('PO Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('po_number')
                            ->required()
                            ->default(fn () => 'PO-'.strtoupper(Str::random(8)))
                            ->unique(ignoreRecord: true),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('purchase_order_status_id')
                            ->relationship('status', 'name')
                            ->required(),
                        DatePicker::make('ordered_at')
                            ->default(now())
                            ->required(),
                        DatePicker::make('received_at'),
                        TextInput::make('total_cost')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                    ]),
            ]);
    }
}
