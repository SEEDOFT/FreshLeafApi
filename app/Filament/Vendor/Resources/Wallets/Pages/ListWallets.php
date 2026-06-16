<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Wallets\Pages;

use App\Filament\Vendor\Resources\Payouts\PayoutResource;
use App\Filament\Vendor\Resources\Wallets\Schemas\WalletTransactionInfolist;
use App\Filament\Vendor\Resources\Wallets\WalletResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Override;

class ListWallets extends Page
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected string $view = 'filament.vendor.pages.wallets.list-wallets';

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

    #[Override]
    protected function getViewData(): array
    {
        $userWallets = Wallet::with('currency')
            ->where('user_id', Auth::id())
            ->get();

        if ($userWallets->isEmpty()) {
            $firstWallet = Wallet::where('user_id', Auth::id())->first();

            if ($firstWallet !== null) {
                $userWallets = collect([$firstWallet]);
            }
        }

        $walletTransactions = [];
        foreach ($userWallets as $wallet) {
            $page = $this->transactionPages[$wallet->id] ?? 1;
            $walletTransactions[$wallet->id] = $wallet->transactions()
                ->with(['type', 'status', 'currency'])
                ->latest()
                ->paginate(10, ['*'], 'page', $page);
        }

        return [
            'userWallets' => $userWallets,
            'walletTransactions' => $walletTransactions,
            'formatWalletBalance' => function (float $balance, ?Currency $currency): string {
                return Order::formatMoney($balance, $currency);
            },
        ];
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('requestWithdrawal')
                ->label(__('shared.wallet.request_withdrawal'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form(function (): array {
                    $userWallets = Wallet::with('currency')
                        ->where('user_id', Auth::id())
                        ->get();

                    return [
                        Select::make('wallet_id')
                            ->label(__('admin.resources.wallet_transaction.wallet'))
                            ->options(
                                $userWallets->mapWithKeys(
                                    fn (Wallet $w): array => [
                                        $w->id => ($w->currency->translated_currency ?? 'Wallet').' Wallet',
                                    ],
                                ),
                            )
                            ->default($userWallets->first()?->id)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => null),
                        TextInput::make('amount')
                            ->label(__('shared.wallet.withdrawal_amount'))
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->maxValue(function (Get $get) use ($userWallets): float {
                                $walletId = $get('wallet_id');
                                $wallet = $userWallets->firstWhere('id', $walletId);

                                return (float) ($wallet->balance ?? 0);
                            }),
                        Select::make('payout_method_id')
                            ->label(__('shared.wallet.payout_method'))
                            ->options(fn () => PayoutMethod::all()->pluck('translated_name', 'id'))
                            ->default(PayoutMethod::BANK_TRANSFER_ID)
                            ->disabled()
                            ->required(),
                    ];
                })
                ->action(function (array $data): void {
                    $wallet = Wallet::with('currency')->where('id', $data['wallet_id'])->firstOrFail();
                    $amount = (float) $data['amount'];
                    $balance = (float) $wallet->balance;

                    if ($amount <= 0) {
                        Notification::make()
                            ->danger()
                            ->title(__('shared.wallet.invalid_amount'))
                            ->send();

                        return;
                    }

                    if ($amount > $balance) {
                        Notification::make()
                            ->danger()
                            ->title(__('shared.wallet.insufficient_balance'))
                            ->send();

                        return;
                    }

                    DB::transaction(function () use ($wallet, $amount, $data): void {
                        $payoutNumber = 'PO-'.Carbon::now()->format('Ymd').'-'.strtoupper(Str::random(6));

                        $payout = Payout::create([
                            'vendor_id' => Auth::id(),
                            'currency_id' => $wallet->currency_id,
                            'payout_method_id' => $data['payout_method_id'],
                            'amount' => $amount,
                            'status_id' => Payout::STATUS_PENDING,
                            'payout_number' => $payoutNumber,
                        ]);

                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'currency_id' => $wallet->currency_id,
                            'wallet_transaction_type_id' => WalletTransactionType::WITHDRAWAL_ID,
                            'wallet_transaction_status_id' => WalletTransactionStatus::PENDING_ID,
                            'amount' => $amount,
                            'reference_id' => $payout->id,
                            'reference_type' => Payout::class,
                            'description' => __('shared.wallet.withdrawal_description', ['number' => $payoutNumber]),
                            'transaction_date' => now(),
                        ])->recordHistory();
                    });

                    Notification::make()
                        ->success()
                        ->title(__('shared.wallet.withdrawal_requested'))
                        ->send();

                    $admins = User::active()
                        ->ofType(UserType::ADMIN_ID)
                        ->get();

                    $notification = Notification::make()
                        ->title(__('shared.wallet.withdrawal_notification_title'))
                        ->body(__('shared.wallet.withdrawal_notification_body', [
                            'vendor' => Auth::id(),
                            'amount' => Order::formatMoney($amount, $wallet->currency),
                        ]))
                        ->icon('heroicon-o-arrow-up-tray')
                        ->success();

                    $notification->sendToDatabase($admins);

                    $this->redirect(PayoutResource::getUrl('index', panel: 'vendor'));
                }),
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

                return WalletTransactionInfolist::configure($schema)
                    ->record($transaction);
            });
    }
}
