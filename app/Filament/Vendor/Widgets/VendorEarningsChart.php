<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Override;

class VendorEarningsChart extends ChartWidget
{
    protected ?string $heading = 'Earnings Trend (30 Days)';

    #[Override]
    protected function getData(): array
    {
        $user = Auth::user();

        // Calculate daily earnings based on items belonging to this vendor in paid orders
        $data = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
            ->where('products.vendor_user_id', $user->id)
            ->where('payment_statuses.code', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.subtotal) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = $data[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Earnings (USD)',
                    'data' => $values,
                    'backgroundColor' => '#4ade80',
                    'borderColor' => '#22c55e',
                ],
            ],
            'labels' => $labels,
        ];
    }

    #[Override]
    protected function getType(): string
    {
        return 'bar';
    }
}
