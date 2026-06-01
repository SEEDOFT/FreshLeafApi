<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Wallets;

use App\Filament\Vendor\Resources\Wallets\Pages\ListWallets;
use App\Filament\Vendor\Resources\Wallets\Pages\ViewWallet;
use App\Filament\Vendor\Resources\Wallets\Schemas\WalletInfolist;
use App\Filament\Vendor\Resources\Wallets\Tables\WalletsTable;
use App\Models\Wallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
            ->where('user_id', Auth::id());

    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListWallets::route('/'),
            'view' => ViewWallet::route('/{record}'),
        ];
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }
}
