<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Pages;

use App\Filament\Vendor\Resources\Payouts\PayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
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
                ->label(__('shared.payout.new_payout') ?? 'New Payout')
                ->form([
                    \Filament\Forms\Components\Select::make('currency_id')
                        ->label(__('shared.payout.currency'))
                        ->options(function () {
                            return \App\Models\Wallet::where('user_id', \Illuminate\Support\Facades\Auth::id())
                                ->with('currency')
                                ->get()
                                ->mapWithKeys(fn ($wallet) => [$wallet->currency_id => $wallet->currency->code]);
                        })
                        ->required()
                        ->live(),
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label(__('shared.payout.amount'))
                        ->numeric()
                        ->required()
                        ->rule(function (\Filament\Forms\Get $get) {
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

                                $pendingAmount = \App\Models\Payout::where('vendor_id', \Illuminate\Support\Facades\Auth::id())
                                    ->where('currency_id', $currencyId)
                                    ->where('status_id', \App\Models\Payout::STATUS_PENDING)
                                    ->sum('amount');

                                $available = $wallet->balance - $pendingAmount;

                                if ($value > $available) {
                                    $fail(__('shared.payout.insufficient_balance') ?? 'Insufficient balance.');
                                }
                            };
                        }),
                    \Filament\Forms\Components\Select::make('payout_method_id')
                        ->label(__('shared.payout.method'))
                        ->relationship('method', 'name_en')
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label(__('shared.payout.notes')),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['vendor_id'] = \Illuminate\Support\Facades\Auth::id();
                    $data['status_id'] = \App\Models\Payout::STATUS_PENDING;
                    $data['payout_number'] = 'PO-'.date('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(6));
                    return $data;
                })
                ->after(function (\App\Models\Payout $record) {
                    $wallet = \App\Models\Wallet::where('user_id', $record->vendor_id)
                        ->where('currency_id', $record->currency_id)
                        ->first();
                        
                    \App\Models\WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'currency_id' => $record->currency_id,
                        'wallet_transaction_type_id' => 2, // Withdrawal
                        'wallet_transaction_status_id' => 1, // Pending
                        'amount' => $record->amount,
                        'reference_id' => $record->id,
                        'reference_type' => \App\Models\Payout::class,
                        'description' => __('shared.payout.withdrawal_description', ['number' => $record->payout_number]) ?? 'Withdrawal request #'.$record->payout_number,
                    ]);
                }),
        ];
    }
}
