<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors;

use App\Filament\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Resources\Vendors\Pages\EditVendor;
use App\Filament\Resources\Vendors\Pages\ListVendors;
use App\Filament\Resources\Vendors\Pages\ViewVendor;
use App\Filament\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Resources\Vendors\Tables\VendorsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class VendorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.accounts');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.vendor.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.vendor.plural_label');
    }

    protected static ?string $slug = 'vendors';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
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
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'view' => ViewVendor::route('/{record}'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }
}
