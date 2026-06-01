<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\PaymentStatus;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Override;

class VendorEarningsChart extends ChartWidget
{
    #[Override]
    public function getHeading(): ?string
    {
        return __('vendor.widgets.earnings_chart_heading');
    }

    #[Override]
    protected int|string|array $columnSpan = 1;

    #[Override]
    protected function getData(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

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

        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $values[] = $data[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('vendor.widgets.earnings_chart_label'),
                    'data' => $values,
                    'backgroundColor' => 'rgba(74, 222, 128, 0.1)',
                    'borderColor' => '#22c55e',
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    #[Override]
    protected function getType(): string
    {
        return 'line';
    }

    #[Override]
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 15,
                        'font' => [
                            'size' => 12,
                            'weight' => '500',
                        ],
                    ],
                ],
                'filler' => [
                    'propagate' => false,
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'scales' => [
                'y' => [
                    'grid' => [
                        'drawBorder' => false,
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
            ],
        ];
    }
}
