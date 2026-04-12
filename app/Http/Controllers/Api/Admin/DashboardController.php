<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardCardResource;
use App\Services\PanelDashboardService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function __construct(private readonly PanelDashboardService $dashboardService) {}

    public function index(): JsonResponse
    {
        $payload = $this->dashboardService->admin();

        return $this->successResponse([
            'module' => $payload['module'],
            'modules' => $payload['modules'],
            'cards' => DashboardCardResource::collection(collect($payload['cards'])),
        ], 'Admin dashboard loaded');
    }

    public function show(string $module): JsonResponse
    {
        try {
            $payload = $this->dashboardService->admin($module);
        } catch (InvalidArgumentException) {
            abort(404, 'Module not found.');
        }

        return $this->successResponse([
            'module' => $payload['module'],
            'modules' => $payload['modules'],
            'cards' => DashboardCardResource::collection(collect($payload['cards'])),
        ], 'Admin module loaded');
    }
}
