<x-filament-panels::page>
    <div class="fl-wallets fl-wallets--compact">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span
                class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.resources.wallet.user') }}:</span>
            @foreach ($this->getUserTypeFilterOptions() as $value => $label)
                @php $active = $this->userTypeFilter === null && $value === 0 || $this->userTypeFilter === $value && $value !== 0; @endphp
                <button wire:click="filterByUserType({{ $value }})"
                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors duration-150
                        @if ($active) bg-primary-500 text-white border-primary-500
                        @else
                            bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 @endif
                    ">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        @if (empty($groupedData))
            <x-filament::empty-state :heading="__('admin.resources.wallet.label')" :description="__('admin.resources.general.not_provided')" />
        @else
            @foreach ($groupedData as $group)
                <x-filament::section :heading="$group['owner']->fullName">
                    <div class="fl-wallets__grid fl-wallets__grid--two">
                        @foreach ($group['wallets'] as $wallet)
                            <div class="fl-wallet-card fl-wallet-card--compact">
                                <x-filament::section :heading="$wallet->currency?->translated_currency ??
                                    __('admin.resources.wallet.label')" :compact="true">
                                    <div class="fl-wallet-details fl-wallet-details--row">
                                        <div class="fl-balance-wrap">
                                            <span class="fl-label">{{ __('admin.resources.wallet.balance') }}</span>
                                            <p class="fl-balance-amount fl-balance-amount--sm">
                                                {{ $formatWalletBalance((float) $wallet->balance, $wallet->currency) }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="fl-label">{{ __('admin.resources.wallet.currency') }}</span>
                                            <p class="fl-meta fl-meta--sm">
                                                @php
                                                    $cName = $wallet->currency?->translated_currency ?? '-';
                                                    $cCode = $wallet->currency?->code ?? '';
                                                @endphp
                                                {{ $cCode !== '' ? "{$cName} ({$cCode})" : $cName }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="fl-label">{{ __('admin.resources.created_at') }}</span>
                                            <p class="fl-meta fl-meta--sm">
                                                {{ $wallet->created_at?->format('h:i A, d M Y') }}</p>
                                        </div>
                                        <div>
                                            <span class="fl-label">{{ __('admin.resources.updated_at') }}</span>
                                            <p class="fl-meta fl-meta--sm">
                                                {{ $wallet->updated_at?->format('h:i A, d M Y') }}</p>
                                        </div>
                                    </div>
                                </x-filament::section>

                                @php $txns = $group['walletTransactions'][$wallet->id] ?? collect(); @endphp
                                @if ($txns->isNotEmpty())
                                    <div class="fl-tx-table-wrap fl-tx-table-wrap--compact">
                                        <table class="fl-tx-table fl-tx-table--sm">
                                            <thead>
                                                <tr>
                                                    <th class="fl-tx-col-amount">
                                                        {{ __('admin.resources.wallet_transaction.amount') }}</th>
                                                    <th class="fl-tx-col-desc">
                                                        {{ __('admin.resources.wallet_transaction.description') }}</th>
                                                    <th class="fl-tx-col-type">
                                                        {{ __('admin.resources.wallet_transaction.type') }}</th>
                                                    <th class="fl-tx-col-status">
                                                        {{ __('admin.resources.wallet_transaction.status') }}</th>
                                                    <th>{{ __('admin.resources.wallet_transaction.transaction_date') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($txns as $txn)
                                                    <tr wire:click="mountAction('viewTransaction', { transaction: {{ $txn->id }} })" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                        <td class="fl-tx-col-amount">
                                                            {{ $formatWalletBalance((float) $txn->amount, $txn->currency) }}
                                                        </td>
                                                        <td class="fl-tx-col-desc">
                                                            {{ $txn->description ?? '-' }}
                                                        </td>
                                                        <td class="fl-tx-col-type">
                                                            @php
                                                                $txnTypeColor = match (
                                                                    $txn->wallet_transaction_type_id
                                                                ) {
                                                                    \App\Models\WalletTransactionType::DEPOSIT_ID
                                                                        => 'success',
                                                                    \App\Models\WalletTransactionType::WITHDRAWAL_ID
                                                                        => 'warning',
                                                                    \App\Models\WalletTransactionType::PAYMENT_ID
                                                                        => 'danger',
                                                                    \App\Models\WalletTransactionType::REFUND_ID
                                                                        => 'info',
                                                                    default => 'gray',
                                                                };
                                                            @endphp
                                                            <x-filament::badge
                                                                :color="$txnTypeColor">{{ $txn->type?->translated_name ?? '-' }}</x-filament::badge>
                                                        </td>
                                                        <td class="fl-tx-col-status">
                                                            @php
                                                                $txnStatusColor = match (
                                                                    $txn->wallet_transaction_status_id
                                                                ) {
                                                                    \App\Models\WalletTransactionStatus::COMPLETED_ID
                                                                        => 'success',
                                                                    \App\Models\WalletTransactionStatus::PENDING_ID
                                                                        => 'warning',
                                                                    \App\Models\WalletTransactionStatus::FAILED_ID,
                                                                    \App\Models\WalletTransactionStatus::CANCELLED_ID
                                                                        => 'danger',
                                                                    default => 'gray',
                                                                };
                                                            @endphp
                                                            <x-filament::badge
                                                                :color="$txnStatusColor">{{ $txn->status?->translated_name ?? '-' }}</x-filament::badge>
                                                        </td>
                                                        <td class="fl-tx-col-date">
                                                            {{ $txn->transaction_date?->format('h:i A, d M Y') ?? ($txn->created_at?->format('h:i A, d M Y') ?? '-') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endforeach
        @endif
    </div>
</x-filament-panels::page>
