<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Schemas;

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
                Section::make(__('admin.resources.payout.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendor.vendorProfile.business_name')
                            ->label(__('admin.resources.payout.business')),
                        TextEntry::make('status.name')
                            ->label(__('admin.resources.payout.status'))
                            ->badge(),
                        TextEntry::make('method.name')
                            ->label(__('admin.resources.payout.method')),
                        TextEntry::make('amount')
                            ->label(__('admin.resources.payout.amount'))
                            ->state(fn (Payout $record): string => format_currency(
                                $record->amount,
                                $record->currency->code
                            )),
                        TextEntry::make('transaction_reference')
                            ->label(__('admin.resources.payout.transaction_ref')),
                        TextEntry::make('processed_at')
                            ->label(__('admin.resources.payout.processed_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('processor.fullName')
                            ->label(__('admin.resources.payout.processed_by')),
                        TextEntry::make('notes')
                            ->label(__('admin.resources.payout.admin_notes'))
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),

                Section::make(__('admin.resources.payout.vendor_financial_details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendor.vendorFinancialDetails.bank_name')
                            ->label(__('admin.resources.vendor.bank_name')),
                        TextEntry::make('vendor.vendorFinancialDetails.account_name')
                            ->label(__('admin.resources.vendor.account_holder')),
                        TextEntry::make('vendor.vendorFinancialDetails.account_number')
                            ->label(__('admin.resources.vendor.account_number')),
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
