<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors;

use App\Filament\Admin\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Admin\Resources\Vendors\Pages\EditVendor;
use App\Filament\Admin\Resources\Vendors\Pages\ListVendors;
use App\Filament\Admin\Resources\Vendors\Pages\ViewVendor;
use App\Filament\Admin\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Admin\Resources\Vendors\Schemas\VendorInfolist;
use App\Filament\Admin\Resources\Vendors\Tables\VendorsTable;
use App\Models\User;
use App\Models\UserType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class VendorResource extends Resource
{
    #[Override]
    protected static ?string $model = User::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    #[Override]
    protected static ?string $slug = 'vendors';

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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_type_id', UserType::VENDOR);
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.vendor.plural_label');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return VendorInfolist::configure($schema);
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
