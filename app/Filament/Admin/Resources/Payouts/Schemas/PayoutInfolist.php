<?php

namespace App\Filament\Resources\Payouts\Schemas;

use Filament\Infolists\Components\TextEntry;
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
                        TextEntry::make('vendor.vendorProfile.business_name')
                            ->label(__('admin.resources.payout.business')),
                        TextEntry::make('status.name')
                            ->label(__('admin.resources.payout.status'))
                            ->badge(),
                        TextEntry::make('method.name')
                            ->label(__('admin.resources.payout.method')),
                        TextEntry::make('amount')
                            ->label(__('admin.resources.payout.amount'))
                            ->money('USD'),
                        TextEntry::make('transaction_reference')
                            ->label(__('admin.resources.payout.transaction_ref'))
                            ->placeholder('-'),
                        TextEntry::make('processed_at')
                            ->label(__('admin.resources.payout.processed_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('processedBy.name')
                            ->label(__('admin.resources.payout.processed_by'))
                            ->placeholder('-'),
                        TextEntry::make('admin_notes')
                            ->label(__('admin.resources.payout.admin_notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
