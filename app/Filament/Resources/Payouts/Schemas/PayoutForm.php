<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payouts\Schemas;

use App\Models\UserType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vendor_user_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'first_name', fn ($query) => $query->where('user_type_id', UserType::VENDOR))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->vendorProfile?->business_name})")
                    ->searchable()
                    ->required(),
                
                Select::make('payout_status_id')
                    ->label('Status')
                    ->relationship('status', 'name')
                    ->required(),
                
                Select::make('payout_method_id')
                    ->label('Method')
                    ->relationship('method', 'name')
                    ->required(),
                
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                TextInput::make('transaction_reference')
                    ->label('Transaction Ref #')
                    ->placeholder('Bank Transfer ID / Receipt #'),
                
                DateTimePicker::make('processed_at')
                    ->label('Processed Date'),
                
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }
}
