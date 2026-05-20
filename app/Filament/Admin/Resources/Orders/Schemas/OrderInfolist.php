<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.order.overview'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order_number')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.order_number').'</strong>'))
                            ->copyable(),
                        TextEntry::make('status.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.status').'</strong>'))
                            ->badge(),
                        TextEntry::make('user.first_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.customer').'</strong>')),
                        TextEntry::make('vendor.business_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.vendor').'</strong>')),
                    ]),

                Section::make(__('admin.resources.order.financials'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_amount')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.total').'</strong>'))
                            ->money('USD'),
                        TextEntry::make('commission_amount')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.commission').'</strong>'))
                            ->money('USD'),
                        TextEntry::make('payment_status.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.payment_status').'</strong>'))
                            ->badge(),
                        TextEntry::make('payment_method.name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.payment_method').'</strong>')),
                    ]),

                Section::make(__('admin.resources.order.delivery_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('delivery_address')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_address').'</strong>'))
                            ->columnSpanFull(),
                        TextEntry::make('delivery_contact_name')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_contact_name').'</strong>')),
                        TextEntry::make('delivery_contact_phone')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.order.delivery_contact_phone').'</strong>')),
                    ]),

                Section::make(__('admin.resources.timestamps'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                            ->dateTime(),
                        TextEntry::make('updated_at')->placeholder(__('admin.resources.general.not_provided'))
                            ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
