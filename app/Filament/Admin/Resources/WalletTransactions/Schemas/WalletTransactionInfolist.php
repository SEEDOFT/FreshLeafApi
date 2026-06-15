<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Schemas;

use App\Models\Order;
use App\Models\Payout;
use App\Models\WalletTransaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class WalletTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('amount')
                            ->label(__('admin.resources.wallet_transaction.amount'))
                            ->formatStateUsing(fn (WalletTransaction $record): string => Order::formatMoney($record->amount, $record->currency))
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->columnSpanFull(),
                        TextEntry::make('wallet.user.fullName')
                            ->label(__('admin.resources.wallet_transaction.user'))
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('type.translated_name')
                            ->label(__('admin.resources.wallet_transaction.type'))
                            ->badge()
                            ->color(fn (WalletTransaction $record) => $record->type?->getColor() ?? 'gray'),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.wallet_transaction.status'))
                            ->badge()
                            ->color(fn (WalletTransaction $record) => $record->status?->getColor() ?? 'gray'),
                        TextEntry::make('transaction_date')
                            ->label(__('admin.resources.wallet_transaction.transaction_date'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('description')
                            ->label(__('admin.resources.wallet_transaction.description'))
                            ->columnSpanFull(),
                        TextEntry::make('reference.order_number')
                            ->label(__('admin.resources.order.order_number'))
                            ->visible(fn (WalletTransaction $record) => $record->reference_type === Order::class),
                        TextEntry::make('reference.payout_number')
                            ->label(__('admin.resources.payout.payout_number'))
                            ->visible(fn (WalletTransaction $record) => $record->reference_type === Payout::class),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
