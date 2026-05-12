<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates;

use App\Filament\Admin\Resources\ExchangeRates\Pages\CreateExchangeRate;
use App\Filament\Admin\Resources\ExchangeRates\Pages\EditExchangeRate;
use App\Filament\Admin\Resources\ExchangeRates\Pages\ListExchangeRates;
use App\Filament\Admin\Resources\ExchangeRates\Pages\ViewExchangeRate;
use App\Filament\Admin\Resources\ExchangeRates\Schemas\ExchangeRateForm;
use App\Filament\Admin\Resources\ExchangeRates\Schemas\ExchangeRateInfolist;
use App\Filament\Admin\Resources\ExchangeRates\Tables\ExchangeRatesTable;
use App\Models\ExchangeRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ExchangeRateResource extends Resource
{
    #[Override]
    protected static ?string $model = ExchangeRate::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.financial');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.exchange_rate.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.exchange_rate.plural_label');
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ExchangeRateForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return ExchangeRateInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ExchangeRatesTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListExchangeRates::route('/'),
            'create' => CreateExchangeRate::route('/create'),
            'view' => ViewExchangeRate::route('/{record}'),
            'edit' => EditExchangeRate::route('/{record}/edit'),
        ];
    }
}
