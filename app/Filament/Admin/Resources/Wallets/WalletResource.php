<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets;

use App\Filament\Admin\Resources\Wallets\Pages\CreateWallet;
use App\Filament\Admin\Resources\Wallets\Pages\EditWallet;
use App\Filament\Admin\Resources\Wallets\Pages\ListWallets;
use App\Filament\Admin\Resources\Wallets\Pages\ViewWallet;
use App\Filament\Admin\Resources\Wallets\Schemas\WalletForm;
use App\Filament\Admin\Resources\Wallets\Schemas\WalletInfolist;
use App\Filament\Admin\Resources\Wallets\Tables\WalletsTable;
use App\Models\UserType;
use App\Models\Wallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class WalletResource extends Resource
{
    #[Override]
    protected static ?string $model = Wallet::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.financial');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.wallet.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.wallet.plural_label');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return WalletForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return WalletInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return WalletsTable::configure($table);
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user', static function (Builder $query): void {
                $query->whereIn('user_type_id', [UserType::ADMIN_ID, UserType::VENDOR_ID, UserType::CONSUMER_ID]);
            })
            ->orderByRaw('
                (
                    SELECT CASE user_type_id
                        WHEN '.UserType::ADMIN_ID.' THEN 1
                        WHEN '.UserType::VENDOR_ID.' THEN 2
                        WHEN '.UserType::CONSUMER_ID.' THEN 3
                        ELSE 4
                    END
                    FROM users
                    WHERE users.id = wallets.user_id
                )
            ');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListWallets::route('/'),
            'create' => CreateWallet::route('/create'),
            'view' => ViewWallet::route('/{record}'),
            'edit' => EditWallet::route('/{record}/edit'),
        ];
    }
}
