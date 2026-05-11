<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiService;
use Illuminate\Http\JsonResponse;

class AiStatusController extends Controller
{
    public function __construct(private AiService $aiService) {}

    /**
     * Checking AI Service Health
     */
    public function check(): JsonResponse
    {
        return static::successResponse([
            'available' => $this->aiService->healthCheck(),
        ], 'AI service status retrieved');
    }
}
