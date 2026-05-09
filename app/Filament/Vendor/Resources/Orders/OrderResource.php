<?php

namespace App\Filament\Vendor\Resources\Orders;

use App\Filament\Vendor\Resources\Orders\Pages\ListOrders;
use App\Filament\Vendor\Resources\Orders\Pages\ViewOrder;
use App\Filament\Vendor\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Vendor\Resources\Orders\Schemas\OrderForm;
use App\Filament\Vendor\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.order.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.order.plural_label');
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
            ->whereHas('items.vendorInventory', fn ($query) => $query->where('vendor_id', auth()->id()));
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
