<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class AdminExchangeRates extends BaseWidget
{
    protected static ?int $sort = 3;

    #[Override]
    protected string $view = 'filament.widgets.stats-overview';

    public function getHeading(): ?string
    {
        return 'EXCHANGE RATES';
    }

    #[Override]
    protected function getColumns(): int
    {
        return 2;
    }

    #[Override]
    public function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'columns' => $this->getColumns(),
            'heading' => $this->getHeading(),
        ];
    }

    #[Override]
    protected function getStats(): array
    {
        $usdToKhr = (float) ExchangeRate::getRate(Currency::USD_ID, Currency::KHR_ID);
        $khrToUsd = (float) ExchangeRate::getRate(Currency::KHR_ID, Currency::USD_ID);

        return [
            Stat::make('USD → KHR', number_format($usdToKhr, 0).' KHR')
                ->description('1 USD')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make('KHR → USD', number_format($khrToUsd, 4).' USD')
                ->description('1 KHR')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),
        ];
    }
}
