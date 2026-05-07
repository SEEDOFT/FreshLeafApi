<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products;

use App\Filament\Vendor\Resources\Products\Pages\CreateProduct;
use App\Filament\Vendor\Resources\Products\Pages\EditProduct;
use App\Filament\Vendor\Resources\Products\Pages\ListProducts;
use App\Filament\Vendor\Resources\Products\Pages\ViewProduct;
use App\Filament\Vendor\Resources\Products\Schemas\ProductForm;
use App\Filament\Vendor\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Vendor\Resources\Products\Tables\ProductsTable;
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

class ProductResource extends Resource
{
    #[Override]
    protected static ?string $model = VendorInventory::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.product.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.product.plural_label');
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User &&
            $user->user_type_id === UserType::VENDOR &&
            $user->user_status_id === UserStatus::ACTIVE &&
            (bool) $user->vendorProfile?->is_verified;
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
        return ProductForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
