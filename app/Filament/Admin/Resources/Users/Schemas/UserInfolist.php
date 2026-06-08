<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\Currency;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->schema([
                        Grid::make(1)
                            ->columnSpanFull()
                            ->schema([
                                ImageEntry::make('image')
                                    ->label(__('admin.profile.avatar'))
                                    ->circular()

                                    ->alignCenter()
                                    ->extraImgAttributes(fn () => [
                                        'class' => 'cursor-zoom-in',
                                        'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                                    ]),
                            ]),
                        TextEntry::make('first_name')->label(__('admin.resources.user.first_name')),
                        TextEntry::make('last_name')->label(__('admin.resources.user.last_name')),
                        TextEntry::make('email')->label(__('admin.resources.user.email')),
                        TextEntry::make('phone_number')->label(__('admin.resources.user.phone')),
                        TextEntry::make('type.translated_name')->label(__('admin.resources.user.type'))
                            ->badge()

                            ->color(fn (User $record): string => match ($record->user_type_id) {
                                UserType::ADMIN_ID => 'success',
                                UserType::VENDOR_ID => 'warning',
                                UserType::CONSUMER_ID => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.translated_name')->label(__('admin.resources.user.status'))

                            ->badge()
                            ->color(fn (User $record): string => match ($record->user_status_id) {
                                UserStatus::ACTIVE_ID => 'success',
                                UserStatus::PENDING_ID => 'warning',
                                UserStatus::INACTIVE_ID, UserStatus::DELETED_ID => 'danger',
                                default => 'gray',
                            }),
                    ]),
                Section::make(__('admin.resources.user.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(__('admin.resources.user.wallets_info'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('currency.translated_currency')

                                    ->label(__('admin.resources.wallet.currency')),
                                TextEntry::make('balance')
                                    ->label(__('admin.resources.wallet.balance'))
                                    ->placeholder('0.00')
                                    ->getStateUsing(function (Wallet $record): string {
                                        $balance = number_format((float) $record->balance, 2);
                                        $symbol = $record->currency->symbol;

                                        return $record->currency->id === Currency::USD_ID
                                            ? "$symbol $balance"
                                            : "$balance $symbol";
                                    }),
                            ]),
                    ]),
                Section::make(__('admin.resources.user.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
