<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\CommissionFee;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Override;

class VendorFinancialOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    #[Override]
    protected string $view = 'filament.widgets.stats-overview';

    public function getHeading(): ?string
    {
        return 'FINANCIAL OVERVIEW';
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
        $user = Auth::user();
        if (! $user instanceof User) {
            return [];
        }

        $walletBalance = (float) (Wallet::query()
            ->where('user_id', $user->id)
            ->whereHas(
                'currency',
                static fn (Builder $query): Builder => $query->where('id', Currency::USD_ID)
            )
            ->value('balance') ?? 0
        );

        $commission = CommissionFee::latest()->first();

        return [
            Stat::make(__('vendor.widgets.wallet_balance'), Number::currency($walletBalance, 'USD'))
                ->description(__('vendor.widgets.wallet_earnings_desc'))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
            Stat::make(
                __('vendor.widgets.platform_commission'),
                $commission ? "{$commission->rate}%" : __('vendor.widgets.not_available')
            )
                ->description($commission->description ?? __('vendor.widgets.commission_fee_desc'))
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('primary'),
        ];
    }
}
