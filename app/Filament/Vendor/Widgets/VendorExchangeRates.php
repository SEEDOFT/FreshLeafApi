<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class VendorExchangeRates extends BaseWidget
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
            Stat::make(__('shared.widgets.usd_to_khr_label'), __('shared.widgets.usd_to_khr', ['rate' => format_number($usdToKhr, 0)]))
                ->description(__('shared.widgets.usd_to_khr_desc'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make(__('shared.widgets.khr_to_usd_label'), __('shared.widgets.khr_to_usd', ['rate' => format_number($khrToUsd, 4)]))
                ->description(__('shared.widgets.khr_to_usd_desc'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),
        ];
    }
}
