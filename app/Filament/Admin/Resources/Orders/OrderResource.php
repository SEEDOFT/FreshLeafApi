<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders;

use App\Filament\Admin\Resources\Orders\Pages\CreateOrder;
use App\Filament\Admin\Resources\Orders\Pages\EditOrder;
use App\Filament\Admin\Resources\Orders\Pages\ListOrders;
use App\Filament\Admin\Resources\Orders\Pages\ViewOrder;
use App\Filament\Admin\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Admin\Resources\Orders\Schemas\OrderForm;
use App\Filament\Admin\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Admin\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
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
        return __('admin.resources.order.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.order.plural_label');
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $admin = Auth::user();

        if (! $admin) {
            throw new AuthenticationException;
        }

        if ($admin->user_type_id !== UserType::ADMIN_ID) {
            throw new AuthenticationException;
        }

        return parent::getEloquentQuery()
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
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
