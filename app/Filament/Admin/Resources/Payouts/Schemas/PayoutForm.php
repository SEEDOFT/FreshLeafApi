<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Schemas;

use App\Models\UserType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vendor_id')
                    ->label(__('admin.resources.payout.vendor'))
                    ->relationship('vendor', 'first_name', fn ($query) => $query->where('user_type_id', UserType::VENDOR))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->vendorProfile?->business_name})")
                    ->searchable()
                    ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),

                Select::make('status_id')
                    ->label(__('admin.resources.payout.status'))
                    ->relationship('status', 'name')
                    ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),

                Select::make('payout_method_id')
                    ->label(__('admin.resources.payout.method'))
                    ->relationship('method', 'name')
                    ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),

                TextInput::make('amount')
                    ->label(__('admin.resources.payout.amount'))
                    ->numeric()
                    ->prefix('$')
                    ->required(fn (string $operation): bool => $operation === 'create')->dehydrated(fn (mixed $state): bool => filled($state)),

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
