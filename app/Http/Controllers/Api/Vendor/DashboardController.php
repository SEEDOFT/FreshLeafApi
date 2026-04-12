<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardCardResource;
use App\Services\PanelDashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly PanelDashboardService $dashboardService) {}

    public function index(): JsonResponse
    {
        return $this->module('dashboard', 'Vendor dashboard loaded');
    }

    public function products(): JsonResponse
    {
        return $this->module('products', 'Vendor products dashboard loaded');
    }

    public function orders(): JsonResponse
    {
        return $this->module('orders', 'Vendor orders dashboard loaded');
    }

    public function payments(): JsonResponse
    {
        return $this->module('payments', 'Vendor payments dashboard loaded');
    }

    public function storeProfile(): JsonResponse
    {
        return $this->module('store-profile', 'Vendor store profile dashboard loaded');
    }

    public function notifications(): JsonResponse
    {
        return $this->module('notifications', 'Vendor notifications dashboard loaded');
    }

    public function help(): JsonResponse
    {
        return $this->module('help', 'Vendor help dashboard loaded');
    }

    private function module(string $module, string $message): JsonResponse
    {
        $payload = $this->dashboardService->vendor($module);

        return $this->successResponse([
            'module' => $payload['module'],
            'modules' => $payload['modules'],
            'cards' => DashboardCardResource::collection(collect($payload['cards'])),
        ], $message);
    }
}
