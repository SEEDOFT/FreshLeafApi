<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ListWallets extends Page
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected string $view = 'filament.admin.pages.wallets.list-wallets';

    public ?int $userTypeFilter = null;

    /** @var array<int, int> */
    public array $transactionPages = [];

    public function nextPage(int $walletId): void
    {
        $this->transactionPages[$walletId] = ($this->transactionPages[$walletId] ?? 1) + 1;
    }

    public function previousPage(int $walletId): void
    {
        $current = $this->transactionPages[$walletId] ?? 1;
        if ($current > 1) {
            $this->transactionPages[$walletId] = $current - 1;
        }
    }

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
        /** @var Builder<Wallet> $walletQuery */
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
                $page = $this->transactionPages[$wallet->id] ?? 1;
                $walletTransactions[$wallet->id] = $wallet->transactions()
                    ->with(['type', 'status', 'currency'])
                    ->latest()
                    ->paginate(6, ['*'], 'page', $page);
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
            ->schema(function (Schema $schema, array $arguments): Schema {
                $transactionId = $arguments['transaction'] ?? null;
                $transaction = WalletTransaction::with(['type', 'status', 'currency', 'reference'])
                    ->where('id', $transactionId)
                    ->first();

                return $schema
                    ->record($transaction)
                    ->components([
                        Section::make()
                            ->schema([
                                TextEntry::make('amount')
                                    ->label(__('admin.resources.wallet_transaction.amount'))
                                    ->formatStateUsing(fn (WalletTransaction $record): string => Order::formatMoney($record->amount, $record->currency))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                TextEntry::make('type.translated_name')
                                    ->label(__('admin.resources.wallet_transaction.type'))
                                    ->badge()
                                    ->color(fn ($record) => match ($record->wallet_transaction_type_id) {
                                        WalletTransactionType::DEPOSIT_ID => 'success',
                                        WalletTransactionType::WITHDRAWAL_ID => 'warning',
                                        WalletTransactionType::PAYMENT_ID => 'danger',
                                        WalletTransactionType::REFUND_ID => 'info',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status.translated_name')
                                    ->label(__('admin.resources.wallet_transaction.status'))
                                    ->badge()
                                    ->color(fn ($record) => match ($record->wallet_transaction_status_id) {
                                        WalletTransactionStatus::COMPLETED_ID => 'success',
                                        WalletTransactionStatus::PENDING_ID => 'warning',
                                        WalletTransactionStatus::FAILED_ID,
                                        WalletTransactionStatus::CANCELLED_ID => 'danger',
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
                                    ->visible(fn ($record) => $record && $record->reference_type === Order::class),
                                TextEntry::make('reference.payout_number')
                                    ->label(__('admin.resources.payout.payout_number'))
                                    ->visible(fn ($record) => $record && $record->reference_type === Payout::class),
                            ])->columns(2),
                    ]);
            });
    }
}
