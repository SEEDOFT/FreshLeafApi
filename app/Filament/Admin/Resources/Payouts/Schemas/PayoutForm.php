<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Schemas;

use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\PayoutMethod;
use App\Models\PayoutStatus;
use App\Models\User;
use App\Models\UserType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vendor_id')
                    ->label(__('admin.resources.payout.vendor'))
                    ->relationship(
                        'vendor',
                        'first_name',
                        fn (Builder $query) => $query->where('user_type_id', UserType::VENDOR_ID)
                            ->whereHas('vendorOrders', function (Builder $query) {
                                $query->where('order_status_id', OrderStatus::DELIVERED_ID)
                                    ->where('is_vendor_paid', false)
                                    ->whereHas('paymentStatus', function (Builder $query) {
                                        $query->where('id', PaymentStatus::COMPLETED_ID);
                                    });
                            })
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (User $record): string => "{$record->fullName} (".($record->vendorProfile->business_name ?? __('N/A')).')'
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),

                TextEntry::make('pending_payout')
                    ->label(__('admin.resources.payout.pending_amount'))
                    ->state(function (Get $get): string {
                        $vendorId = $get('vendor_id');
                        if (! $vendorId) {
                            return '$0.00';
                        }

                        $pendingAmount = (float) OrderItem::query()
                            ->whereHas(
                                'order',
                                static function (Builder $query) use ($vendorId): void {
                                    $query->where('vendor_id', $vendorId)
                                        ->where('order_status_id', OrderStatus::DELIVERED_ID)
                                        ->where('is_vendor_paid', false)
                                        ->whereHas(
                                            'paymentStatus',
                                            static function (Builder $query): void {
                                                $query->where('id', PaymentStatus::COMPLETED_ID);
                                            }
                                        );
                                }
                            )
                            ->sum('vendor_net_amount');

                        return '$'.number_format($pendingAmount, 2);
                    })
                    ->visible(fn (Get $get): bool => filled($get('vendor_id'))),

                Select::make('status_id')
                    ->label(__('admin.resources.payout.status'))
                    ->disabled()
                    ->options(fn () => PayoutStatus::where('id', PayoutStatus::PAID_ID)->get()->pluck('translated_name', 'id'))
                    ->default(PayoutStatus::PAID_ID)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),

                Select::make('payout_method_id')
                    ->label(__('admin.resources.payout.method'))
                    ->disabled()
                    ->options(fn () => PayoutMethod::where('id', PayoutMethod::BANK_TRANSFER_ID)->get()->pluck('translated_name', 'id'))
                    ->default(PayoutMethod::BANK_TRANSFER_ID)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),

                TextInput::make('amount')
                    ->label(__('admin.resources.payout.amount'))
                    ->numeric()
                    ->prefix('$')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),

                TextInput::make('transaction_reference')
                    ->label(__('admin.resources.payout.transaction_ref'))
                    ->placeholder(__('admin.resources.payout.transaction_ref')),

                DateTimePicker::make('processed_at')
                    ->label(__('admin.resources.payout.processed_date')),

                Textarea::make('notes')
                    ->label(__('admin.resources.payout.admin_notes'))
                    ->columnSpanFull(),
            ]);
    }
}
