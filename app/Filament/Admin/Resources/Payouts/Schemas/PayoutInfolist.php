<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Schemas;

use App\Models\Payout;
use App\Models\PaymentMethod;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.payout.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('payout_number')
                            ->label(__('admin.resources.payout.payout_number')),
                        TextEntry::make('vendor.vendorProfile.business_name')
                            ->label(__('admin.resources.payout.business')),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.payout.status'))
                            ->badge()
                            ->color(fn (Payout $record): string => $record->status?->getColor() ?? 'gray'),
                        TextEntry::make('method.translated_name')
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
                    ->relationship('vendor.vendorFinancialDetails')
                    ->columns(2)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('bank_name')
                                    ->label(__('admin.resources.vendor.bank_name')),
                                TextEntry::make('account_name')
                                    ->label(__('admin.resources.vendor.account_holder')),
                                TextEntry::make('account_number')
                                    ->label(__('admin.resources.vendor.account_number')),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                ImageEntry::make('qr_code')
                                    ->label(__('admin.resources.vendor.qr_code'))
                                    ->getStateUsing(fn (?PaymentMethod $record) => $record?->qr_code
                                        ? (resolve_image_url($record->qr_code) ?: route('admin.documents.show', ['path' => $record->qr_code])) : null
                                    )
                                    ->disk(null)
                                    ->extraImgAttributes(fn () => [
                                        'class' => 'cursor-zoom-in',
                                        'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
