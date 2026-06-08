<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Schemas;

use App\Models\Currency;
use App\Models\Payout;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('shared.payout.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('amount')
                            ->label(__('shared.payout.amount'))
                            ->state(function (Payout $record) {
                                if ($record->currency?->id === Currency::USD_ID) {
                                    return '$ '.$record->amount;
                                }

                                if ($record->currency?->id === Currency::KHR_ID) {
                                    return $record->amount.'៛';
                                }

                                return $record->amount;
                            }),
                        TextEntry::make('status.name')
                            ->label(__('shared.payout.status'))
                            ->badge(),
                        TextEntry::make('method.name')
                            ->label(__('shared.payout.method')),
                        TextEntry::make('transaction_reference')
                            ->label(__('shared.payout.transaction_ref')),
                        TextEntry::make('processed_at')
                            ->label(__('shared.payout.processed_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('notes')
                            ->label(__('shared.payout.admin_notes'))
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label(__('shared.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('shared.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),

                Section::make(__('shared.payout.financial_details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendor.vendorFinancialDetails.bank_name')
                            ->label(__('shared.auth.register.bank_name')),
                        TextEntry::make('vendor.vendorFinancialDetails.account_name')
                            ->label(__('shared.auth.register.account_holder')),
                        TextEntry::make('vendor.vendorFinancialDetails.account_number')
                            ->label(__('shared.auth.register.account_number')),
                        ImageEntry::make('vendor.vendorFinancialDetails.qr_code')
                            ->label(__('shared.auth.register.qr_code'))
                            ->getStateUsing(function (Payout $record): ?string {
                                $qrCode = $record->vendor->vendorFinancialDetails()->value('qr_code');

                                if (! $qrCode) {
                                    return null;
                                }

                                if (! Storage::disk('local')->exists($qrCode)) {
                                    return null;
                                }

                                return URL::temporarySignedRoute(
                                    'private.storage',
                                    Carbon::now()->addWeeks(3),
                                    ['path' => $qrCode],
                                );
                            })
                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                    ]),
            ]);
    }
}
