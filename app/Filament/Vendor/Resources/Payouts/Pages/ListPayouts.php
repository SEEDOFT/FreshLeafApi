<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Pages;

use App\Filament\Vendor\Resources\Payouts\PayoutResource;
use App\Models\Payout;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Override;

class ListPayouts extends ListRecords
{
    #[Override]
    protected static string $resource = PayoutResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('shared.payout.new_payout'))
                ->form([
                    Select::make('currency_id')
                        ->label(__('shared.payout.currency'))
                        ->options(function () {
                            return Wallet::where('user_id', Auth::id())
                                ->with('currency')
                                ->get()
                                ->mapWithKeys(fn ($wallet) => [$wallet->currency_id => $wallet->currency->code]);
                        })
                        ->required()
                        ->live(),
                    TextInput::make('amount')
                        ->label(__('shared.payout.amount'))
                        ->numeric()
                        ->required()
                        ->rules([
                            function (\Filament\Forms\Get $get): \Closure {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $currencyId = $get('currency_id');
                                    if (! $currencyId) return;

                                    $wallet = \App\Models\Wallet::where('user_id', \Illuminate\Support\Facades\Auth::id())
                                        ->where('currency_id', $currencyId)
                                        ->first();

                                    if (! $wallet) {
                                        $fail(__('shared.payout.insufficient_balance') ?? 'Insufficient balance.');
                                        return;
                                    }

                                    if ($value > $wallet->balance) {
                                        $fail('The payout amount cannot exceed your wallet balance.');
                                        return;
                                    }

                                    $pendingAmount = \App\Models\Payout::where('vendor_id', \Illuminate\Support\Facades\Auth::id())
                                        ->where('currency_id', $currencyId)
                                        ->where('status_id', \App\Models\Payout::STATUS_PENDING)
                                        ->sum('amount');

                                    if ($pendingAmount > 0 && $pendingAmount == $wallet->balance) {
                                        $fail('Your entire wallet balance is currently locked in a pending payout. Please wait for it to be approved or rejected.');
                                        return;
                                    }

                                    $available = $wallet->balance - $pendingAmount;

                                    if ($value > $available) {
                                        $fail("Insufficient available balance. You have a pending payout of {$pendingAmount}. Your available balance is {$available}.");
                                    }
                                };
                            }
                        ]),
                    Select::make('payout_method_id')
                        ->label(__('shared.payout.method'))
                        ->relationship('method', 'name_en')
                        ->required(),
                    Textarea::make('notes')
                        ->label(__('shared.payout.notes')),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['vendor_id'] = Auth::id();
                    $data['status_id'] = Payout::STATUS_PENDING;
                    $data['payout_number'] = 'PO-'.date('Ymd').'-'.strtoupper(Str::random(6));

                    return $data;
                })
                ->after(function (Payout $record) {
                    $wallet = Wallet::where('user_id', $record->vendor_id)
                        ->where('currency_id', $record->currency_id)
                        ->first();

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'currency_id' => $record->currency_id,
                        'wallet_transaction_type_id' => 2, // Withdrawal
                        'wallet_transaction_status_id' => 1, // Pending
                        'amount' => $record->amount,
                        'reference_id' => $record->id,
                        'reference_type' => Payout::class,
                        'description' => __('shared.payout.withdrawal_description', ['number' => $record->payout_number]),
                    ]);
                }),
        ];
    }
}
