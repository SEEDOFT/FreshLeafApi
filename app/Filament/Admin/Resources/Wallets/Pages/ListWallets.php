<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use App\Filament\Admin\Resources\WalletTransactions\Schemas\WalletTransactionInfolist;
use App\Models\Currency;
use App\Models\Order;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Override;

class ListWallets extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected string $view = 'filament.admin.pages.wallets.list-wallets';

    public ?int $selectedUserId = null;

    /** @var array<int, int> */
    public array $transactionPages = [];

    public function nextTransactionPage(int $walletId): void
    {
        $this->transactionPages[$walletId] = ($this->transactionPages[$walletId] ?? 1) + 1;
    }

    public function previousTransactionPage(int $walletId): void
    {
        $current = $this->transactionPages[$walletId] ?? 1;
        if ($current > 1) {
            $this->transactionPages[$walletId] = $current - 1;
        }
    }

    public function clearSelectedUser(): void
    {
        $this->selectedUserId = null;
        $this->transactionPages = [];
    }

    public function table(Table $table): Table
    {
        $livewire = $this;

        return $table
            ->query(User::query()->whereIn('user_type_id', [UserType::ADMIN_ID, UserType::VENDOR_ID, UserType::CONSUMER_ID]))
            ->recordAction('viewWallets')
            ->recordClasses(fn (User $record) => match ($record->user_status_id) {
                UserStatus::PENDING_ID => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                UserStatus::ACTIVE_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                UserStatus::INACTIVE_ID => 'bg-gray-50 dark:bg-gray-900/50 border-l-4 border-gray-400',
                UserStatus::REJECTED_ID, UserStatus::DELETED_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('admin.resources.user.first_name'))
                    ->getStateUsing(fn (User $record): string => $record->first_name)
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label(__('admin.resources.user.last_name'))
                    ->getStateUsing(fn (User $record): string => $record->last_name)
                    ->searchable(),
                TextColumn::make('user_type_id')
                    ->label(__('admin.resources.user.type'))
                    ->formatStateUsing(fn ($state) => UserType::find($state)?->translated_name)
                    ->badge()
                    ->color(fn ($record) => $record->type?->getColor() ?? 'gray')
                    ->sortable(),
                TextColumn::make('user_status_id')
                    ->label(__('admin.resources.user.status'))
                    ->formatStateUsing(fn ($state) => UserStatus::find($state)?->translated_name)
                    ->badge()
                    ->color(fn ($record) => $record->status?->getColor() ?? 'gray')
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('admin.resources.user.email'))
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label(__('admin.resources.user.phone'))
                    ->searchable(),

            ])
            ->actions([
                Action::make('viewWallets')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->action(function (User $record) use ($livewire) {
                        $livewire->selectedUserId = $record->id;
                    }),
            ]);
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->viewTransactionAction()->hidden(),
            Action::make('seedSandboxWallet')
                ->label('Seed Sandbox Wallet')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('Consumer')
                        ->options(function () {
                            return User::where('user_type_id', UserType::CONSUMER_ID)
                                ->get()
                                ->mapWithKeys(fn ($user) => [$user->id => $user->fullName])
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                    Select::make('currency_id')
                        ->label('Currency')
                        ->options(function () {
                            return Currency::all()
                                ->mapWithKeys(fn ($currency) => [$currency->id => "{$currency->translated_currency} ({$currency->code})"])
                                ->toArray();
                        })
                        ->default(Currency::USD_ID)
                        ->required(),
                    TextInput::make('amount')
                        ->label('Amount to Seed')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $userId = $data['user_id'];
                    $currencyId = (int) $data['currency_id'];
                    $amount = (float) $data['amount'];

                    DB::transaction(function () use ($userId, $currencyId, $amount) {
                        $user = User::findOrFail($userId);

                        $wallet = Wallet::firstOrCreate(
                            ['user_id' => $user->id, 'currency_id' => $currencyId],
                            ['balance' => 0]
                        );

                        $wallet->balance += $amount;
                        $wallet->save();

                        $transaction = new WalletTransaction([
                            'wallet_id' => $wallet->id,
                            'currency_id' => $wallet->currency_id,
                            'wallet_transaction_type_id' => WalletTransactionType::DEPOSIT_ID,
                            'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                            'amount' => $amount,
                            'description' => 'Sandbox seed',
                            'transaction_date' => now(),
                        ]);
                        $transaction->save();
                        $transaction->recordHistory();

                        $wallet->histories()->create(collect($wallet->getAttributes())->except(['id', 'created_at', 'updated_at', 'deleted_at'])->toArray());
                    });
                })
                ->successNotificationTitle('Sandbox wallet seeded successfully'),
        ];
    }

    #[Override]
    protected function getViewData(): array
    {
        if (! $this->selectedUserId) {
            return [
                'groupedData' => [],
                'formatWalletBalance' => static function (float $balance, ?Currency $currency): string {
                    return Order::formatMoney($balance, $currency);
                },
            ];
        }

        /** @var Builder<Wallet> $walletQuery */
        $walletQuery = Wallet::with('currency')
            ->where('user_id', $this->selectedUserId);

        $groupedData = [];

        foreach ($walletQuery->get() as $wallet) {
            $owner = User::with('type')->find($wallet->user_id);

            if ($owner === null) {
                continue;
            }

            $ownerKey = $wallet->user_id;

            if (! isset($groupedData[$ownerKey])) {
                $groupedData[$ownerKey] = [
                    'owner' => $owner,
                    'wallets' => [],
                    'walletTransactions' => [],
                    'is_authenticated' => $owner->id === auth()->id(),
                    'user_type_label' => $owner->type?->translated_name,
                    'user_type_color' => $owner->type?->getColor() ?? 'gray',
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

                return WalletTransactionInfolist::configure($schema)
                    ->record($transaction);
            });
    }
}
