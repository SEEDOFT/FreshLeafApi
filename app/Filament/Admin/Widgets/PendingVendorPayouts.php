<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Payouts\PayoutResource;
use App\Models\Payout;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingVendorPayouts extends TableWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.resources.payout.plural_label'))
            ->query(fn () => Payout::query()->where('status_id', Payout::STATUS_PENDING)->latest())
            ->columns([
                TextColumn::make('vendor.vendorProfile.business_name')
                    ->label(__('admin.resources.payout.business')),

                TextColumn::make('amount')
                    ->label(__('admin.resources.payout.amount'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('method.name')
                    ->label(__('admin.resources.payout.method')),

                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('admin.resources.payout.approve')) // Using approve label since it's the main action for pending
                    ->icon('heroicon-m-eye')
                    ->url(fn (Payout $record): string => PayoutResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
