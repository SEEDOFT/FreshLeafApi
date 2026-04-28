<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payouts;

use App\Filament\Resources\Payouts\Pages\CreatePayout;
use App\Filament\Resources\Payouts\Pages\EditPayout;
use App\Filament\Resources\Payouts\Pages\ListPayouts;
use App\Filament\Resources\Payouts\Pages\ViewPayout;
use App\Filament\Resources\Payouts\Schemas\PayoutForm;
use App\Filament\Resources\Payouts\Schemas\PayoutInfolist;
use App\Filament\Resources\Payouts\Tables\PayoutsTable;
use App\Models\Payout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.financial');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.payout.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.payout.plural_label');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return PayoutForm::configure($schema);
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
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
            'create' => CreatePayout::route('/create'),
            'view' => ViewPayout::route('/{record}'),
            'edit' => EditPayout::route('/{record}/edit'),
        ];
    }
}
