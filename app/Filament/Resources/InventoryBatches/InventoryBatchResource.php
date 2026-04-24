<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryBatches;

use App\Filament\Resources\InventoryBatches\Pages\CreateInventoryBatch;
use App\Filament\Resources\InventoryBatches\Pages\EditInventoryBatch;
use App\Filament\Resources\InventoryBatches\Pages\ListInventoryBatches;
use App\Filament\Resources\InventoryBatches\Schemas\InventoryBatchForm;
use App\Filament\Resources\InventoryBatches\Tables\InventoryBatchesTable;
use App\Models\InventoryBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

class InventoryBatchResource extends Resource
{
    protected static ?string $model = InventoryBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return InventoryBatchForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return InventoryBatchesTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListInventoryBatches::route('/'),
            'create' => CreateInventoryBatch::route('/create'),
            'edit' => EditInventoryBatch::route('/{record}/edit'),
        ];
    }
}
