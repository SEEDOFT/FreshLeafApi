<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions;

use App\Filament\Admin\Resources\WalletTransactions\Pages\CreateWalletTransaction;
use App\Filament\Admin\Resources\WalletTransactions\Pages\EditWalletTransaction;
use App\Filament\Admin\Resources\WalletTransactions\Pages\ListWalletTransactions;
use App\Filament\Resources\WalletTransactions\Schemas\WalletTransactionForm;
use App\Filament\Resources\WalletTransactions\Tables\WalletTransactionsTable;
use App\Models\WalletTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.financial');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.wallet_transaction.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.wallet_transaction.plural_label');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return WalletTransactionForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return WalletTransactionsTable::configure($table);
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
            'index' => ListWalletTransactions::route('/'),
            'create' => CreateWalletTransaction::route('/create'),
            'edit' => EditWalletTransaction::route('/{record}/edit'),
        ];
    }
}
