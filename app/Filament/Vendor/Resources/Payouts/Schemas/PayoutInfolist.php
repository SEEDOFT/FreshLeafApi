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
                            ->label(new HtmlString('<strong>'.__('shared.payout.amount').'</strong>'))
                            ->money('USD'),
                        TextEntry::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.payout.status').'</strong>'))
                            ->badge(),
                        TextEntry::make('method.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.payout.method').'</strong>')),
                        TextEntry::make('transaction_reference')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.payout.transaction_ref').'</strong>'))
                            ->placeholder('-'),
                        TextEntry::make('processed_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.payout.processed_at').'</strong>'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('admin_notes')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.payout.admin_notes').'</strong>'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('shared.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.created_at').'</strong>'))
                            ->dateTime(),
                        TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('shared.updated_at').'</strong>'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
