<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Schemas;

use App\Models\Currency;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class WalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->components([
                Section::make(__('admin.resources.wallet.label'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.first_name').'</strong>'))
                            ->getStateUsing(static fn (Wallet $record) => $record->user->first_name),
                        TextEntry::make('last_name')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.last_name').'</strong>'))
                            ->getStateUsing(static fn (Wallet $record) => $record->user->last_name),
                        TextEntry::make('currency.code')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.currency').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('balance')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.balance').'</strong>'))
                            ->getStateUsing(static function (Wallet $record): string {
                                $id = $record->currency->id ?? null;
                                $symbol = $record->currency->symbol ?? '';
                                $balance = number_format((float) $record->balance, 2);

                                return $id === Currency::USD_ID
                                ? "{$symbol} {$balance}"
                                    : "{$balance} {$symbol}";
                            }),
                        TextEntry::make('user.type.translated_name')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.type').'</strong>'))
                            ->badge()
                            ->color(fn (Wallet $record): string => match ($record->user->user_type_id) {
                                UserType::ADMIN_ID => 'success',
                                UserType::VENDOR_ID => 'warning',
                                UserType::CONSUMER_ID => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('user.status.translated_name')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.status').'</strong>'))
                            ->badge()
                            ->color(fn (Wallet $record): string => match ($record->user->user_status_id) {
                                UserStatus::ACTIVE_ID => 'success',
                                UserStatus::PENDING_ID => 'warning',
                                UserStatus::INACTIVE_ID, UserStatus::DELETED_ID => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->placeholder($notProvided)
                            ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
