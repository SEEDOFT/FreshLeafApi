<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Tables;

use App\Models\Payout;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Services\MoneyService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->recordClasses(fn (Payout $record) => match ($record->status_id) {
                Payout::STATUS_PENDING => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                Payout::STATUS_PAID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                Payout::STATUS_FAILED => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                TextColumn::make('vendor.vendorProfile.business_name')
                    ->label(__('admin.resources.payout.business'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payout_number')
                    ->label(__('admin.resources.payout.payout_number'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('amount')
                    ->label(__('admin.resources.payout.amount'))
                    ->formatStateUsing(fn (Payout $record): string => format_currency(
                        $record->amount,
                        $record->currency->code
                    ))
                    ->sortable(),

                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.payout.status'))
                    ->badge()
                    ->color(fn (Payout $record): string => $record->status?->getColor() ?? 'gray'),

                TextColumn::make('method.translated_name')
                    ->label(__('admin.resources.payout.method')),

                TextColumn::make('processed_at')
                    ->label(__('admin.resources.payout.processed_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('approve')
                    ->label(__('admin.resources.payout.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Payout $record): bool => $record->status_id === Payout::STATUS_PENDING)
                    ->form([
                        TextInput::make('transaction_reference')
                            ->label(__('admin.resources.payout.transaction_ref'))
                            ->placeholder(__('admin.resources.payout.transaction_ref')),
                        Textarea::make('admin_notes')
                            ->label(__('admin.resources.payout.admin_notes')),
                    ])
                    ->action(function (Payout $record, array $data, Action $action): void {
                        DB::transaction(function () use ($record, $data, $action) {
                            $wallet = Wallet::where('user_id', $record->vendor_id)
                                ->where('currency_id', $record->currency_id)
                                ->lockForUpdate()
                                ->first();

                            if (! $wallet || $record->amount > $wallet->balance) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('shared.payout.insufficient_balance') ?? 'Insufficient Balance')
                                    ->body('The vendor does not have enough balance in their wallet to fulfill this payout. Please reject it.')
                                    ->send();

                                $action->halt();
                            }

                            $record->update([
                                'status_id' => Payout::STATUS_PAID,
                                'processed_by_admin_id' => Auth::id(),
                                'processed_at' => Carbon::now(),
                                'transaction_reference' => $data['transaction_reference'] ?? null,
                                'notes' => $data['admin_notes'] ?? $record->notes,
                            ]);

                            $walletTransaction = WalletTransaction::where('reference_id', $record->id)
                                ->where('reference_type', Payout::class)
                                ->first();

                            if ($walletTransaction) {
                                $walletTransaction->update([
                                    'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                                ]);

                                $walletTransaction->recordHistory();
                            }

                            $vendorWallet = Wallet::where('user_id', $record->vendor_id)
                                ->where('currency_id', $record->currency_id)
                                ->first();

                            if ($vendorWallet) {
                                $newBalance = MoneyService::sub((string) $vendorWallet->balance, (string) $record->amount);
                                $vendorWallet->update(['balance' => $newBalance]);

                                $vendorWallet->histories()->create([
                                    'user_id' => $vendorWallet->user_id,
                                    'currency_id' => $vendorWallet->currency_id,
                                    'balance' => $newBalance,
                                ]);
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title(__('admin.resources.payout.approved_success'))
                            ->send();

                        Notification::make()
                            ->success()
                            ->title(__('admin.resources.payout.notifications.approved_title'))
                            ->body(__('admin.resources.payout.notifications.approved_body', [
                                'number' => $record->payout_number,
                                'amount' => format_currency($record->amount, $record->currency->code),
                            ]))
                            ->sendToDatabase($record->vendor)
                            ->broadcast($record->vendor);
                    }),
                Action::make('reject')
                    ->label(__('admin.resources.payout.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Payout $record): bool => $record->status_id === Payout::STATUS_PENDING)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label(__('admin.resources.payout.admin_notes'))
                            ->required(),
                    ])
                    ->action(function (Payout $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            $record->update([
                                'status_id' => Payout::STATUS_FAILED,
                                'processed_by_admin_id' => Auth::id(),
                                'processed_at' => Carbon::now(),
                                'notes' => $data['admin_notes'],
                            ]);

                            $walletTransaction = WalletTransaction::where('reference_id', $record->id)
                                ->where('reference_type', Payout::class)
                                ->first();

                            if ($walletTransaction) {
                                $walletTransaction->update([
                                    'wallet_transaction_status_id' => WalletTransactionStatus::CANCELLED_ID,
                                ]);

                                $walletTransaction->recordHistory();
                            }
                        });

                        Notification::make()
                            ->danger()
                            ->title(__('admin.resources.payout.rejected_success'))
                            ->send();

                        Notification::make()
                            ->danger()
                            ->title(__('admin.resources.payout.notifications.rejected_title'))
                            ->body(__('admin.resources.payout.notifications.rejected_body', [
                                'number' => $record->payout_number,
                                'reason' => $data['admin_notes'],
                            ]))
                            ->sendToDatabase($record->vendor)
                            ->broadcast($record->vendor);
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
