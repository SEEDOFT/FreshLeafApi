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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
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
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status.name')
                    ->label(__('admin.resources.payout.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Paid' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('method.name')
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
                    ->action(function (Payout $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            $record->update([
                                'status_id' => Payout::STATUS_PAID,
                                'processed_by_admin_id' => Auth::id(),
                                'processed_at' => now(),
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

                            $wallet = Wallet::where('user_id', $record->vendor_id)
                                ->where('currency_id', $record->currency_id)
                                ->first();

                            if ($wallet) {
                                $newBalance = MoneyService::sub((string) $wallet->balance, (string) $record->amount);
                                $wallet->update(['balance' => $newBalance]);

                                $wallet->histories()->create([
                                    'user_id' => $wallet->user_id,
                                    'currency_id' => $wallet->currency_id,
                                    'balance' => $newBalance,
                                ]);
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title(__('admin.resources.payout.approved_success'))
                            ->send();
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
                                'processed_at' => now(),
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
