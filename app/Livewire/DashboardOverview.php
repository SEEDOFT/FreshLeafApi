<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CommissionFee;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorInventory;
use App\Models\VendorProfile;
use App\Models\Wallet;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardOverview extends Component
{
    public string $panelId = 'admin';

    /** @var array<int, array{label: string, value: string, icon: string, iconClass: string, description: string, badgeClass?: string}> */
    public array $financialStats = [];

    /** @var array<int, array{label: string, value: string, icon: string, iconClass: string, description: string, subClass?: string}> */
    public array $platformStats = [];

    /** @var array<int, array{from: string, to: string, rate: string, trend: string, trendClass: string, iconClass: string}> */
    public array $exchangeRates = [];

    /** @var array{maxHeight: int, bars: array<int, array{height: int, highlight: bool}>, labels: array<int, string>, title: string} */
    public array $chartData = ['maxHeight' => 0, 'bars' => [], 'labels' => [], 'title' => ''];

    public function mount(): void
    {
        $panel = Filament::getCurrentPanel();
        $this->panelId = $panel?->getId() ?? 'admin';

        $this->loadDashboardData();
    }

    public function render(): View
    {
        return view('livewire.dashboard-overview');
    }

    private function loadDashboardData(): void
    {
        if ($this->panelId === 'admin') {
            $this->loadAdminData();
        } else {
            $this->loadVendorData();
        }

        $this->loadExchangeRates();
        $this->loadChartData();
    }

    private function loadAdminData(): void
    {
        $totalRevenue = (float) Order::query()
            ->whereHas(
                'paymentStatus',
                static fn (Builder $query): Builder => $query->where('id', PaymentStatus::COMPLETED_ID)
            )
            ->sum('total_amount');

        $totalCommission = (float) OrderItem::query()
            ->whereHas(
                'order',
                static function (Builder $query): void {
                    $query->where('order_status_id', OrderStatus::DELIVERED_ID)
                        ->whereHas(
                            'paymentStatus',
                            static function (Builder $query): void {
                                $query->where('id', PaymentStatus::COMPLETED_ID);
                            }
                        );
                }
            )
            ->sum('commission_amount');

        $pendingPayouts = (float) OrderItem::query()
            ->whereHas(
                'order',
                static function (Builder $query): void {
                    $query->where('order_status_id', OrderStatus::DELIVERED_ID)
                        ->where('is_vendor_paid', false)
                        ->whereHas(
                            'paymentStatus',
                            static function (Builder $query): void {
                                $query->where('id', PaymentStatus::COMPLETED_ID);
                            }
                        );
                }
            )
            ->sum('vendor_net_amount');
        $commission = CommissionFee::latest()->first();

        $consumerCount = User::where('user_type_id', UserType::CONSUMER_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->count();
        $vendorCount = User::where('user_type_id', UserType::VENDOR_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->count();
        $pendingVendors = VendorProfile::where('is_verified', false)->count();
        $productCount = VendorInventory::count();

        $rateText = $commission ? "{$commission->rate}%" : __('vendor.widgets.not_available');
        $rateDesc = $commission->description ?? __('admin.widgets.platform_fee_desc');

        $this->financialStats = [
            [
                'label' => __('admin.widgets.commission.platform_commission'),
                'value' => '$'.number_format($totalCommission, 2),
                'icon' => 'heroicon-o-currency-dollar',
                'iconClass' => 'green',
                'description' => __('admin.widgets.commission.platform_commission_desc'),
            ],
            [
                'label' => __('admin.widgets.commission.pending_payouts'),
                'value' => '$'.number_format($pendingPayouts, 2),
                'icon' => 'heroicon-o-clock',
                'iconClass' => 'amber',
                'description' => '',
                'badgeClass' => 'pending-badge',
            ],
            [
                'label' => __('admin.resources.product.revenue'),
                'value' => '$'.number_format($totalRevenue, 2),
                'icon' => 'heroicon-o-arrow-trending-up',
                'iconClass' => 'teal',
                'description' => __('admin.resources.order.paid_description'),
            ],
            [
                'label' => __('admin.widgets.platform_fee_label'),
                'value' => $rateText,
                'icon' => 'heroicon-o-receipt-percent',
                'iconClass' => 'purple',
                'description' => $rateDesc,
            ],
        ];

        $this->platformStats = [
            [
                'label' => __('admin.resources.user.plural_label'),
                'value' => (string) $consumerCount,
                'icon' => 'heroicon-o-users',
                'iconClass' => 'blue',
                'description' => __('admin.resources.user.registered_description'),
            ],
            [
                'label' => __('admin.resources.vendor.plural_label'),
                'value' => (string) $vendorCount,
                'icon' => 'heroicon-o-building-storefront',
                'iconClass' => 'green',
                'description' => $pendingVendors > 0
                    ? '<svg class="inline-block h-3 w-3 fill-green-500" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg> '.$pendingVendors.' '.__('admin.resources.vendor.pending_approval')
                    : '',
                'subClass' => $pendingVendors > 0 ? 'pos' : '',
            ],
            [
                'label' => __('admin.widgets.products_listed'),
                'value' => (string) $productCount,
                'icon' => 'heroicon-o-cube',
                'iconClass' => 'rose',
                'description' => __('admin.widgets.products_listed_desc'),
            ],
        ];
    }

    private function loadVendorData(): void
    {
        $user = Auth::user();
        $userId = $user?->id;

        $productCount = 0;
        $todayOrders = 0;
        $walletBalance = 0.0;

        if ($userId) {
            $productCount = VendorInventory::where('vendor_id', $userId)->count();

            $todayOrders = Order::query()
                ->whereHas(
                    'items.vendorInventory',
                    static fn (Builder $query) => $query->where('vendor_id', $userId)
                )
                ->whereDate('created_at', Carbon::now())
                ->count();

            $walletBalance = (float) (Wallet::query()
                ->where('user_id', $userId)
                ->whereHas('currency', static fn (Builder $query) => $query->where('id', Currency::USD_ID))
                ->value('balance') ?? 0);
        }

        $commission = CommissionFee::latest()->first();
        $rateText = $commission ? "{$commission->rate}%" : __('vendor.widgets.not_available');
        $rateDesc = $commission->description ?? __('vendor.widgets.commission_fee_desc');

        $this->financialStats = [
            [
                'label' => __('vendor.widgets.my_products'),
                'value' => (string) $productCount,
                'icon' => 'heroicon-o-cube',
                'iconClass' => 'green',
                'description' => __('vendor.widgets.total_products_desc'),
            ],
            [
                'label' => __('vendor.widgets.orders_today'),
                'value' => (string) $todayOrders,
                'icon' => 'heroicon-o-shopping-bag',
                'iconClass' => 'blue',
                'description' => __('vendor.widgets.orders_today_desc'),
            ],
            [
                'label' => __('vendor.widgets.wallet_balance'),
                'value' => '$'.number_format($walletBalance, 2),
                'icon' => 'heroicon-o-wallet',
                'iconClass' => 'teal',
                'description' => __('vendor.widgets.wallet_earnings_desc'),
            ],
            [
                'label' => __('vendor.widgets.platform_commission'),
                'value' => $rateText,
                'icon' => 'heroicon-o-receipt-percent',
                'iconClass' => 'purple',
                'description' => $rateDesc,
            ],
        ];

        $this->platformStats = [];
    }

    private function loadExchangeRates(): void
    {
        $usdToKhr = (float) ExchangeRate::getRate(Currency::USD_ID, Currency::KHR_ID);
        $khrToUsd = (float) ExchangeRate::getRate(Currency::KHR_ID, Currency::USD_ID);

        $this->exchangeRates = [
            [
                'from' => 'USD',
                'to' => 'KHR',
                'rate' => number_format($usdToKhr, 0).' KHR',
                'trend' => '1 USD',
                'trendClass' => 'pos',
                'iconClass' => 'amber',
            ],
            [
                'from' => 'KHR',
                'to' => 'USD',
                'rate' => number_format($khrToUsd, 4).' USD',
                'trend' => '1 KHR',
                'trendClass' => 'neg',
                'iconClass' => 'blue',
            ],
        ];
    }

    private function loadChartData(): void
    {
        $isAdmin = $this->panelId === 'admin';

        if ($isAdmin) {
            $data = Order::query()
                ->whereHas(
                    'paymentStatus',
                    static fn (Builder $query): Builder => $query->where('id', PaymentStatus::COMPLETED_ID)
                )
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray();
            $chartLabel = __('admin.widgets.revenue_chart.heading');
            $periodLabel = __('admin.widgets.revenue_chart.period');
        } else {
            $user = Auth::user();
            $data = [];

            if ($user) {
                $data = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('vendor_inventories', 'order_items.vendor_inventory_id', '=', 'vendor_inventories.id')
                    ->join('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
                    ->where('vendor_inventories.vendor_id', $user->id)
                    ->where('payment_statuses.id', PaymentStatus::COMPLETED_ID)
                    ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
                    ->select(
                        DB::raw('DATE(orders.created_at) as date'),
                        DB::raw('SUM(order_items.vendor_net_amount) as total')
                    )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('total', 'date')
                    ->toArray();
            }

            $chartLabel = __('vendor.widgets.earnings_chart_heading');
            $periodLabel = __('vendor.widgets.earnings_chart_period');
        }

        $values = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $values[] = (float) ($data[$date] ?? 0);
        }

        $maxValue = max($values);
        $maxHeight = 80;

        $bars = [];
        foreach ($values as $v) {
            $height = $maxValue > 0 ? max(4, (int) round(($v / $maxValue) * $maxHeight)) : 4;
            $bars[] = [
                'height' => $height,
                'highlight' => $height > 35,
            ];
        }

        $this->chartData = [
            'maxHeight' => $maxHeight,
            'bars' => $bars,
            'labels' => [$labels[0], $labels[7], $labels[14], $labels[21], $labels[29]],
            'title' => $chartLabel,
            'period' => $periodLabel ?? __('admin.widgets.revenue_chart.period'),
        ];
    }
}
