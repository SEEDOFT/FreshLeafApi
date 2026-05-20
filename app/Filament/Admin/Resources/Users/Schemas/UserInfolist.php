<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Constants\StorageDirectory;
use App\Models\Currency;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->components([
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.first_name').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('last_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.last_name').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('email')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.email').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('phone_number')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.phone').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('type.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.type').'</strong>'))
                            ->badge()
                            ->placeholder($notProvided)
                            ->color(fn (User $record): string => match ($record->user_type_id) {
                                UserType::ADMIN_ID => 'success',
                                UserType::VENDOR_ID => 'warning',
                                UserType::CONSUMER_ID => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.status').'</strong>'))
                            ->placeholder($notProvided)
                            ->badge()
                            ->color(fn (User $record): string => match ($record->user_status_id) {
                                UserStatus::ACTIVE_ID => 'success',
                                UserStatus::PENDING_ID => 'warning',
                                UserStatus::INACTIVE_ID, UserStatus::DELETED_ID => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make(__('admin.resources.user.personal_info'))
                    ->schema([
                        ImageEntry::make('image')
                            ->label(new HtmlString('<strong>'.__('admin.profile.avatar').'</strong>'))
                            ->disk('public')
                            ->getStateUsing(fn (User $record) => $record->image ? StorageDirectory::USERS.'/'.$record->image : null)
                            ->circular()
                            ->imageSize(200),
                    ]),

                Section::make(__('admin.resources.user.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.wallets_info').'</strong>'))
                            ->schema([
                                TextEntry::make('currency.translated_currency')->placeholder(__('admin.resources.general.not_provided'))
                                    ->label(new HtmlString('<strong>'.__('admin.resources.wallet.currency').'</strong>'))
                                    ->placeholder($notProvided),
                                TextEntry::make('balance')->placeholder(__('admin.resources.general.not_provided'))
                                    ->label(new HtmlString('<strong>'.__('admin.resources.wallet.balance').'</strong>'))
                                    ->placeholder('0.00')
                                    ->getStateUsing(function (Wallet $record): string {
                                        $balance = number_format((float) $record->balance, 2);
                                        $symbol = $record->currency->symbol;

                                        return $record->currency->id === Currency::USD_ID
                                            ? "$symbol $balance"
                                            : "$balance $symbol";
                                    }),
                            ])
                            ->columns(2),
                    ]),

                Section::make(__('admin.resources.user.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
