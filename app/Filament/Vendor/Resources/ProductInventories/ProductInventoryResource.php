<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories;

use App\Filament\Admin\Resources\VendorInventories\RelationManagers\AdjustmentsRelationManager;
use App\Filament\Vendor\Resources\ProductInventories\Pages\CreateProductInventory;
use App\Filament\Vendor\Resources\ProductInventories\Pages\EditProductInventory;
use App\Filament\Vendor\Resources\ProductInventories\Pages\ListProductInventories;
use App\Filament\Vendor\Resources\ProductInventories\Pages\ViewProductInventory;
use App\Filament\Vendor\Resources\ProductInventories\Schemas\ProductInventoryForm;
use App\Filament\Vendor\Resources\ProductInventories\Schemas\ProductInventoryInfolist;
use App\Filament\Vendor\Resources\ProductInventories\Tables\ProductInventoryTable;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorInventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ProductInventoryResource extends Resource
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
    public static function getNavigationLabel(): string
    {
        return __('shared.product.product_inventory');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('shared.product.product_inventory');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('shared.product.product_inventory');
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User &&
            $user->user_type_id === UserType::VENDOR_ID &&
            $user->user_status_id === UserStatus::ACTIVE_ID &&
            (bool) $user->vendorProfile->is_verified;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', auth()->id());
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ProductInventoryForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return ProductInventoryInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ProductInventoryTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            AdjustmentsRelationManager::class,
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListProductInventories::route('/'),
            'create' => CreateProductInventory::route('/create'),
            'view' => ViewProductInventory::route('/{record}'),
            'edit' => EditProductInventory::route('/{record}/edit'),
        ];
    }
}
