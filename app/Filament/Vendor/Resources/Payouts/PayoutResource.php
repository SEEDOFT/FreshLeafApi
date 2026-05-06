<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts;

use App\Filament\Vendor\Resources\Payouts\Pages\ListPayouts;
use App\Filament\Vendor\Resources\Payouts\Pages\ViewPayout;
use App\Filament\Vendor\Resources\Payouts\Schemas\PayoutInfolist;
use App\Filament\Vendor\Resources\Payouts\Tables\PayoutsTable;
use App\Models\Payout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class PayoutResource extends Resource
{
    #[Override]
    protected static ?string $model = Payout::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_user_id', auth()->id());
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return PayoutInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return PayoutsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
            'view' => ViewPayout::route('/{record}'),
        ];
    }
}
