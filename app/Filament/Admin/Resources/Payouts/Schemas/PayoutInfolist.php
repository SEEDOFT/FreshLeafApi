<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.payout.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('vendor.vendorProfile.business_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.business')),
                        TextEntry::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.status'))
                            ->badge(),
                        TextEntry::make('method.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.method')),
                        TextEntry::make('amount')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.amount'))
                            ->money('USD'),
                        TextEntry::make('transaction_reference')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.transaction_ref'))
                            ->placeholder('-'),
                        TextEntry::make('processed_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.processed_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('processedBy.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.processed_by'))
                            ->placeholder('-'),
                        TextEntry::make('admin_notes')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.payout.admin_notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.created_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
