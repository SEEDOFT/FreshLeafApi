<x-filament-panels::page>
    @if (! $selectedUserId)
        <div wire:key="wallets-table-view" class="mb-8">
            {{ $this->table }}
        </div>
    @else
        <div wire:key="wallets-detail-view" class="mb-4">
            <x-filament::button wire:click="clearSelectedUser" color="gray" icon="heroicon-m-arrow-left">
                Back to Users
            </x-filament::button>
        </div>
        <div class="fl-wallets fl-wallets--compact">

        @if (empty($groupedData))
            <x-filament::empty-state :heading="__('admin.resources.wallet.label')" :description="__('admin.resources.general.not_provided')" />
        @else
            @foreach ($groupedData as $group)
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <span>{{ $group['owner']->fullName }}</span>
                            <x-filament::badge :color="$group['user_type_color']" size="sm">
                                {{ $group['user_type_label'] }}
                            </x-filament::badge>
                            @if ($group['is_authenticated'])
                                <x-filament::badge color="primary" size="sm" icon="heroicon-m-user">
                                    {{ __('admin.chat.you') }}
                                </x-filament::badge>
                            @endif
                        </div>
                    </x-slot>

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
                                        @if($txns->lastPage() > 1)
                                            <div class="flex justify-between items-center mt-4 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                                                <x-filament::button wire:click="previousTransactionPage({{ $wallet->id }})" color="gray" size="sm" :disabled="$txns->onFirstPage()">
                                                    &lt;
                                                </x-filament::button>
                                                
                                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                    Page {{ $txns->currentPage() }} of {{ max(1, $txns->lastPage()) }}
                                                </span>
                                                
                                                <x-filament::button wire:click="nextTransactionPage({{ $wallet->id }})" color="gray" size="sm" :disabled="!$txns->hasMorePages()">
                                                    &gt;
                                                </x-filament::button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endforeach
        @endif
    </div>
    @endif
</x-filament-panels::page>
