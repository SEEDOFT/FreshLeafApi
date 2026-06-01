<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders;

use App\Filament\Vendor\Resources\Orders\Pages\ListOrders;
use App\Filament\Vendor\Resources\Orders\Pages\ViewOrder;
use App\Filament\Vendor\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Vendor\Resources\Orders\Schemas\OrderForm;
use App\Filament\Vendor\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Vendor\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;

class OrderResource extends Resource
{
    #[Override]
    protected static ?string $model = Order::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.sales');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('shared.order.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('shared.order.plural_label');
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $vendor = Auth::user();
        if (! $vendor) {
            return null;
        }

        $count = static::getEloquentQuery()
            ->where('order_status_id', OrderStatus::PENDING_ID)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    #[Override]
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User &&
            $user->user_type_id === UserType::VENDOR_ID &&
            $user->user_status_id === UserStatus::ACTIVE_ID &&
            (bool) $user->vendorProfile->is_verified;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $vendor = Auth::user();

        if (! $vendor) {
            throw new AuthenticationException;
        }

        if ($vendor->user_type_id !== UserType::VENDOR_ID) {
            throw new AuthenticationException;
        }

        return parent::getEloquentQuery()
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
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
