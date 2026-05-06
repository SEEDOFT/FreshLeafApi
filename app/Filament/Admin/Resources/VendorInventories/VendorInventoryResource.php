<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories;

use App\Filament\Admin\Resources\VendorInventories\Pages\ListVendorInventories;
use App\Filament\Admin\Resources\VendorInventories\Pages\ViewVendorInventory;
use App\Filament\Admin\Resources\VendorInventories\Schemas\VendorInventoryInfolist;
use App\Filament\Admin\Resources\VendorInventories\Tables\VendorInventoryTable;
use App\Models\VendorInventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class VendorInventoryResource extends Resource
{
    #[Override]
    protected static ?string $model = VendorInventory::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.vendor_inventory.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.vendor_inventory.plural_label');
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return VendorInventoryInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return VendorInventoryTable::configure($table);
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
            'index' => ListVendorInventories::route('/'),
            'view' => ViewVendorInventory::route('/{record}'),
        ];
    }
}
