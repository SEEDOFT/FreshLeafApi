<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates;

use App\Filament\Admin\Resources\ExchangeRates\Pages\ManageExchangeRate;
use App\Models\ExchangeRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
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
    public static function getPages(): array
    {
        return [
            'index' => ManageExchangeRate::route('/'),
        ];
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }
}
