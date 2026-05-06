<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Currency;
use App\Models\Wallet;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.first_name').'</strong>')),
                        TextEntry::make('last_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.last_name').'</strong>')),
                        TextEntry::make('email')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.email').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('phone_number')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.phone').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('type.name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.type').'</strong>'))
                            ->badge()
                            ->placeholder('-')
                            ->color(static fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Vendor' => 'warning',
                                'Consumer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.status').'</strong>'))
                            ->placeholder('-')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make(__('admin.resources.user.personal_info'))
                    ->schema([
                        ImageEntry::make('image')
                            ->label(new HtmlString('<strong>'.__('admin.profile.avatar').'</strong>'))
                            ->disk('public')
                            ->circular()
                            ->imageSize(200)
                            ->defaultImageUrl(Storage::disk('public')->url('users/user.png')),
                    ]),

                Section::make(__('admin.resources.user.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.wallets_info').'</strong>'))
                            ->schema([
                                TextEntry::make('currency.name')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.wallet.currency').'</strong>'))
                                    ->placeholder('-'),
                                TextEntry::make('balance')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.wallet.balance').'</strong>'))
                                    ->placeholder('0.00')
                                    ->getStateUsing(static function (Wallet $record): string {
                                        $id = $record->currency?->id;
                                        $symbol = $record->currency->symbol ?? '';
                                        $balance = number_format((float) $record->balance, 2);

                                        return $id === Currency::USD_ID
                                            ? "{$symbol} {$balance}"
                                            : "{$balance} {$symbol}";
                                    }),
                            ])
                            ->columns(2),
                    ]),

                Section::make(__('admin.resources.user.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
