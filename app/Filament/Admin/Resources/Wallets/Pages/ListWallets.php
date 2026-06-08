<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Resources\Pages\Page;
use Override;

class ListWallets extends Page
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected string $view = 'filament.admin.pages.wallets.list-wallets';

    public ?int $userTypeFilter = null;

    #[Override]
    public function getTitle(): string
    {
        return __('admin.resources.wallet.plural_label');
    }

    /**
     * @return array<int, string>
     */
    public function getUserTypeFilterOptions(): array
    {
        return [
            0 => __('admin.resources.wallet.filter_all'),
            UserType::ADMIN_ID => __('admin.resources.wallet.filter_admin'),
            UserType::VENDOR_ID => __('admin.resources.wallet.filter_vendor'),
            UserType::CONSUMER_ID => __('admin.resources.wallet.filter_consumer'),
        ];
    }

    public function filterByUserType(int $typeId): void
    {
        $this->userTypeFilter = $typeId === 0 ? null : $typeId;
    }

    #[Override]
    protected function getViewData(): array
    {
        $walletQuery = Wallet::with('currency')
            ->orderBy('user_id');

        if ($this->userTypeFilter !== null) {
            $relation = match ($this->userTypeFilter) {
                UserType::ADMIN_ID => 'admin',
                UserType::VENDOR_ID => 'vendor',
                UserType::CONSUMER_ID => 'user',
                default => null,
            };

            if ($relation !== null) {
                $walletQuery->whereHas($relation);
            }
        }

        $groupedData = [];

        foreach ($walletQuery->get() as $wallet) {
            $owner = User::find($wallet->user_id);

            if ($owner === null) {
                continue;
            }

            $ownerKey = $wallet->user_id;

            if (! isset($groupedData[$ownerKey])) {
                $groupedData[$ownerKey] = [
                    'owner' => $owner,
                    'wallets' => [],
                    'walletTransactions' => [],
                ];
            }

            $groupedData[$ownerKey]['wallets'][] = $wallet;
        }

        foreach ($groupedData as $ownerKey => $group) {
            $walletTransactions = [];
            foreach ($group['wallets'] as $wallet) {
                $walletTransactions[$wallet->id] = $wallet->transactions()
                    ->with(['type', 'status', 'currency'])
                    ->latest()
                    ->limit(10)
                    ->get();
            }
            $groupedData[$ownerKey]['walletTransactions'] = $walletTransactions;
        }

        return [
            'groupedData' => array_values($groupedData),
            'formatWalletBalance' => static function (float $balance, ?Currency $currency): string {
                return Order::formatMoney($balance, $currency);
            },
        ];
    }

    public function viewTransactionAction(): Action
    {
        return Action::make('viewTransaction')
            ->modalHeading(__('admin.resources.wallet_transaction.label') ?? 'Transaction Details')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('shared.general.close') ?? 'Close')
            ->infolist(function (Infolist $infolist, array $arguments): Infolist {
                $transactionId = $arguments['transaction'] ?? null;
                $transaction = \App\Models\WalletTransaction::with(['type', 'status', 'currency', 'reference'])->find($transactionId);

                return $infolist
                    ->record($transaction)
                    ->schema([
                        Section::make()
                            ->schema([
                                TextEntry::make('amount')
                                    ->label(__('admin.resources.wallet_transaction.amount'))
                                    ->state(function ($record) {
                                        return Order::formatMoney((float) $record->amount, $record->currency);
                                    })
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                                TextEntry::make('type.translated_name')
                                    ->label(__('admin.resources.wallet_transaction.type'))
                                    ->badge()
                                    ->color(fn ($record) => match ($record->wallet_transaction_type_id) {
                                        \App\Models\WalletTransactionType::DEPOSIT_ID => 'success',
                                        \App\Models\WalletTransactionType::WITHDRAWAL_ID => 'warning',
                                        \App\Models\WalletTransactionType::PAYMENT_ID => 'danger',
                                        \App\Models\WalletTransactionType::REFUND_ID => 'info',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status.translated_name')
                                    ->label(__('admin.resources.wallet_transaction.status'))
                                    ->badge()
                                    ->color(fn ($record) => match ($record->wallet_transaction_status_id) {
                                        \App\Models\WalletTransactionStatus::COMPLETED_ID => 'success',
                                        \App\Models\WalletTransactionStatus::PENDING_ID => 'warning',
                                        \App\Models\WalletTransactionStatus::FAILED_ID,
                                        \App\Models\WalletTransactionStatus::CANCELLED_ID => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('transaction_date')
                                    ->label(__('admin.resources.wallet_transaction.transaction_date'))
                                    ->dateTime('h:i A, d M Y'),
                                TextEntry::make('description')
                                    ->label(__('admin.resources.wallet_transaction.description'))
                                    ->columnSpanFull(),
                                TextEntry::make('reference.order_number')
                                    ->label(__('admin.resources.order.order_number'))
                                    ->visible(fn ($record) => $record && $record->reference_type === \App\Models\Order::class),
                                TextEntry::make('reference.payout_number')
                                    ->label(__('admin.resources.payout.payout_number'))
                                    ->visible(fn ($record) => $record && $record->reference_type === \App\Models\Payout::class),
                            ])->columns(2),
                    ]);
            });
    }
}
