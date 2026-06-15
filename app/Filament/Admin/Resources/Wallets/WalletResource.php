<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets;

use App\Filament\Admin\Resources\Wallets\Pages\CreateWallet;
use App\Filament\Admin\Resources\Wallets\Pages\EditWallet;
use App\Filament\Admin\Resources\Wallets\Pages\ListWallets;
use App\Filament\Admin\Resources\Wallets\Schemas\WalletForm;
use App\Models\Order;
use App\Models\UserType;
use App\Models\Wallet;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.first_name')
                    ->label(__('admin.resources.wallet.user'))
                    ->formatStateUsing(fn ($state, Wallet $record) => $record->user?->fullName ?? '-')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('user.type.translated_name')
                    ->label(__('admin.resources.user.type') ?? 'User Type')
                    ->badge()
                    ->color(fn ($state, Wallet $record) => $record->user?->type?->getColor() ?? 'gray'),

                TextColumn::make('user.status.translated_name')
                    ->label(__('admin.resources.user.status') ?? 'Status')
                    ->badge()
                    ->color(fn ($state, Wallet $record) => $record->user?->status?->getColor() ?? 'gray'),

                TextColumn::make('currency.code')
                    ->label(__('admin.resources.wallet.currency'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('balance')
                    ->label(__('admin.resources.wallet.balance'))
                    ->formatStateUsing(fn ($state, Wallet $record) => Order::formatMoney((float) $state, $record->currency))
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup(
                Group::make('user_id')
                    ->label('User')
                    ->getTitleFromRecordUsing(fn (Wallet $record): string => $record->user?->fullName ?? 'Unknown')
            )
            ->filters([
                SelectFilter::make('user_type_id')
                    ->label('User Type')
                    ->options([
                        UserType::ADMIN_ID => __('admin.resources.wallet.filter_admin'),
                        UserType::VENDOR_ID => __('admin.resources.wallet.filter_vendor'),
                        UserType::CONSUMER_ID => __('admin.resources.wallet.filter_consumer'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('user', fn ($q) => $q->where('user_type_id', $data['value']));
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label('View Details'),
            ]);
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
            'edit' => EditWallet::route('/{record}/edit'),
        ];
    }
}
