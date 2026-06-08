<x-filament-panels::page>
    <div class="fl-wallets">
        @if($userWallets->isEmpty())
            <x-filament::empty-state
                :heading="__('admin.resources.wallet.label')"
                :description="__('admin.resources.general.not_provided')"
            />
        @else
            <div class="fl-wallets__grid @if($userWallets->count() >= 2) fl-wallets__grid--two @endif">
                @foreach($userWallets as $wallet)
                    <div class="fl-wallet-card">
                        <x-filament::section :heading="$wallet->currency?->translated_currency ?? __('admin.resources.wallet.label') . ' #' . $wallet->id">
                            <div class="fl-wallet-details">
                                <div class="fl-balance-wrap">
                                    <span class="fl-label">{{ __('admin.resources.wallet.balance') }}</span>
                                    <p class="fl-balance-amount">{{ $formatWalletBalance((float) $wallet->balance, $wallet->currency) }}</p>
                                </div>
                                <div>
                                    <span class="fl-label">{{ __('admin.resources.wallet.currency') }}</span>
                                    <p class="fl-meta">
                                        @php
                                            $cName = $wallet->currency?->translated_currency ?? '-';
                                            $cCode = $wallet->currency?->code ?? '';
                                        @endphp
                                        {{ $cCode !== '' ? "{$cName} ({$cCode})" : $cName }}
                                    </p>
                                </div>
                                <div>
                                    <span class="fl-label">{{ __('admin.resources.created_at') }}</span>
                                    <p class="fl-meta">{{ $wallet->created_at?->format('h:i A, d M Y') ?? '-' }}</p>
                                </div>
                                <div>
                                    <span class="fl-label">{{ __('admin.resources.updated_at') }}</span>
                                    <p class="fl-meta">{{ $wallet->updated_at?->format('h:i A, d M Y') ?? '-' }}</p>
                                </div>
                            </div>
                        </x-filament::section>

                        <x-filament::section :heading="__('admin.resources.wallet_transaction.plural_label')">
                            @php $txns = $walletTransactions[$wallet->id] ?? collect(); @endphp
                            @if($txns->isNotEmpty())
                                <div class="fl-tx-table-wrap">
                                    <table class="fl-tx-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('admin.resources.wallet_transaction.type') }}</th>
                                                <th class="fl-tx-col-amount">{{ __('admin.resources.wallet_transaction.amount') }}</th>
                                                <th class="fl-tx-col-status">{{ __('admin.resources.wallet_transaction.status') }}</th>
                                                <th>{{ __('admin.resources.wallet_transaction.transaction_date') }}</th>
                                                <th>{{ __('admin.resources.wallet_transaction.description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($txns as $txn)
                                                <tr wire:click="mountAction('viewTransaction', { transaction: {{ $txn->id }} })" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                    <td>
                                                        <x-filament::badge>{{ $txn->type?->translated_name ?? '-' }}</x-filament::badge>
                                                    </td>
                                                    <td class="fl-tx-col-amount">
                                                        {{ $formatWalletBalance((float) $txn->amount, $txn->currency) }}
                                                    </td>
                                                    <td class="fl-tx-col-status">
                                                        @php
                                                            $txnStatusColor = match ($txn->wallet_transaction_status_id) {
                                                                \App\Models\WalletTransactionStatus::COMPLETED_ID => 'success',
                                                                \App\Models\WalletTransactionStatus::PENDING_ID => 'warning',
                                                                \App\Models\WalletTransactionStatus::FAILED_ID,
                                                                \App\Models\WalletTransactionStatus::CANCELLED_ID => 'danger',
                                                                default => 'gray',
                                                            };
                                                        @endphp
                                                        <x-filament::badge :color="$txnStatusColor">{{ $txn->status?->translated_name ?? '-' }}</x-filament::badge>
                                                    </td>
                                                    <td class="fl-tx-col-date">{{ $txn->transaction_date?->format('h:i A, d M Y') ?? $txn->created_at?->format('h:i A, d M Y') ?? '-' }}</td>
                                                    <td class="fl-tx-col-desc" title="{{ $txn->description ?? '' }}">{{ $txn->description ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if($txns->lastPage() > 1)
                                        <div class="flex justify-between items-center mt-4 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
                                            <x-filament::button wire:click="previousPage({{ $wallet->id }})" color="gray" size="sm" :disabled="$txns->onFirstPage()">
                                                &lt;
                                            </x-filament::button>
                                            
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Page {{ $txns->currentPage() }} of {{ max(1, $txns->lastPage()) }}
                                            </span>
                                            
                                            <x-filament::button wire:click="nextPage({{ $wallet->id }})" color="gray" size="sm" :disabled="!$txns->hasMorePages()">
                                                &gt;
                                            </x-filament::button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="fl-label">{{ __('admin.resources.wallet.no_transactions') }}</p>
                            @endif
                        </x-filament::section>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
