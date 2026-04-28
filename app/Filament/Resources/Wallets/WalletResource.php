<?php

declare(strict_types=1);

namespace App\Filament\Resources\Wallets;

use App\Filament\Resources\Wallets\Pages\CreateWallet;
use App\Filament\Resources\Wallets\Pages\EditWallet;
use App\Filament\Resources\Wallets\Pages\ListWallets;
use App\Filament\Resources\Wallets\Schemas\WalletForm;
use App\Filament\Resources\Wallets\Tables\WalletsTable;
use App\Models\Wallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.financial');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.wallet.label');
    }

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
    public static function table(Table $table): Table
    {
        return WalletsTable::configure($table);
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
            'index' => ListWallets::route('/'),
            'create' => CreateWallet::route('/create'),
            'edit' => EditWallet::route('/{record}/edit'),
        ];
    }
}
