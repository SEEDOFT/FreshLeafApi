<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Schemas;

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
                Section::make(__('shared.payout.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('amount')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.payout.amount'))
                            ->money('USD'),
                        TextEntry::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.payout.status'))
                            ->badge(),
                        TextEntry::make('method.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.payout.method')),
                        TextEntry::make('transaction_reference')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.payout.transaction_ref'))
                            ->placeholder('-'),
                        TextEntry::make('processed_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.payout.processed_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('admin_notes')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.payout.admin_notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(__('shared.updated_at'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
