<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use App\Services\MoneyService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('app:seed-wallet {user_id} {--amount=1000} {--khr=4000000}')]
#[Description('Seed a user wallet with test money')]
class SeedWalletMoney extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $amountUsd = $this->option('amount');
        $amountKhr = $this->option('khr');

        $user = User::find($userId);

        if (! $user) {
            $this->error('User not found.');

            return 1;
        }

        /**
         * @var Collection<int, Wallet> $wallets
         */
        $wallets = Wallet::where('user_id', $user->id)->get();

        if ($wallets->isEmpty()) {
            $this->error('User has no wallets.');

            return 1;
        }

        foreach ($wallets as $wallet) {
            $amount = $wallet->currency_id === Currency::USD_ID ? $amountUsd : $amountKhr;

            $wallet->balance = (float) MoneyService::add((string) $wallet->balance, (string) $amount);
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'currency_id' => $wallet->currency_id,
                'wallet_transaction_type_id' => WalletTransactionType::DEPOSIT_ID,
                'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                'amount' => $amount,
                'description' => 'Sandbox Money Seed',
                'transaction_date' => now(),
            ]);

            $transaction->recordHistory();
        }

        $this->info("Successfully seeded wallets for user {$user->first_name}.");

        return 0;
    }
}
