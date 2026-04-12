<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorStatus;
use InvalidArgumentException;

class PanelDashboardService
{
    /**
     * @var array<int, string>
     */
    private const ADMIN_MODULES = [
        'dashboard',
        'vendors',
        'catalog',
        'orders',
        'payments',
        'users',
        'help-center',
        'settings',
    ];

    /**
     * @var array<int, string>
     */
    private const VENDOR_MODULES = [
        'dashboard',
        'products',
        'orders',
        'payments',
        'store-profile',
        'notifications',
        'help',
    ];

    /**
     * @return array{module: string, modules: array<int, string>, cards: array<int, array{label: string, value: int, tone: string}>}
     */
    public function admin(string $module = 'dashboard'): array
    {
        if (! in_array($module, self::ADMIN_MODULES, true)) {
            throw new InvalidArgumentException('Unsupported admin module.');
        }

        return [
            'module' => $module,
            'modules' => self::ADMIN_MODULES,
            'cards' => match ($module) {
                'vendors' => [
                    ['label' => 'Total Vendors', 'value' => Vendor::query()->count(), 'tone' => 'neutral'],
                    ['label' => 'Pending', 'value' => Vendor::query()->where('status_id', VendorStatus::PENDING)->count(), 'tone' => 'warning'],
                    ['label' => 'Approved', 'value' => Vendor::query()->where('status_id', VendorStatus::ACTIVE)->count(), 'tone' => 'success'],
                    ['label' => 'Rejected', 'value' => Vendor::query()->where('status_id', VendorStatus::INACTIVE)->count(), 'tone' => 'error'],
                ],
                'catalog' => [
                    ['label' => 'Products', 'value' => Product::query()->count(), 'tone' => 'neutral'],
                    ['label' => 'Orders', 'value' => Order::query()->count(), 'tone' => 'info'],
                    ['label' => 'Payments', 'value' => Payment::query()->count(), 'tone' => 'warning'],
                    ['label' => 'Users', 'value' => User::query()->count(), 'tone' => 'success'],
                ],
                default => [
                    ['label' => 'Vendors', 'value' => Vendor::query()->count(), 'tone' => 'neutral'],
                    ['label' => 'Products', 'value' => Product::query()->count(), 'tone' => 'info'],
                    ['label' => 'Orders', 'value' => Order::query()->count(), 'tone' => 'warning'],
                    ['label' => 'Payments', 'value' => Payment::query()->count(), 'tone' => 'success'],
                ],
            },
        ];
    }

    /**
     * @return array{module: string, modules: array<int, string>, cards: array<int, array{label: string, value: int, tone: string}>}
     */
    public function vendor(string $module = 'dashboard'): array
    {
        if (! in_array($module, self::VENDOR_MODULES, true)) {
            throw new InvalidArgumentException('Unsupported vendor module.');
        }

        return [
            'module' => $module,
            'modules' => self::VENDOR_MODULES,
            'cards' => match ($module) {
                'products' => [
                    ['label' => 'Products', 'value' => Product::query()->count(), 'tone' => 'neutral'],
                    ['label' => 'Orders', 'value' => Order::query()->count(), 'tone' => 'info'],
                    ['label' => 'Payments', 'value' => Payment::query()->count(), 'tone' => 'warning'],
                    ['label' => 'Vendors', 'value' => Vendor::query()->count(), 'tone' => 'success'],
                ],
                default => [
                    ['label' => 'Orders', 'value' => Order::query()->count(), 'tone' => 'neutral'],
                    ['label' => 'Payments', 'value' => Payment::query()->count(), 'tone' => 'warning'],
                    ['label' => 'Products', 'value' => Product::query()->count(), 'tone' => 'info'],
                    ['label' => 'Store Health', 'value' => 100, 'tone' => 'success'],
                ],
            },
        ];
    }
}
