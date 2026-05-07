<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use App\Http\Requests\Test\SendTestNotificationRequest;
use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TestNotificationController extends Controller
{
    /**
     * Send a test notification to a specific user by user_id.
     *
     * POST /api/v1/test/notifications
     */
    public function send(SendTestNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = (int) $validated['user_id'];
        $title = $validated['title'];
        $body = $validated['body'];
        $data = $validated['data'] ?? [];

        Log::info('TestNotification endpoint called', [
            'user_id' => $userId,
            'title' => $title,
        ]);

        $user = User::find($userId);

        if (! $user instanceof User) {
            return static::errorResponse(
                'User not found',
                404,
                ['user_id' => $userId]
            );
        }

        $fcmTokens = $user->routeNotificationForFcm();
        $tokenCount = count($fcmTokens);

        if ($tokenCount === 0) {
            return static::errorResponse(
                'User has no registered devices',
                400,
                ['user_id' => $userId, 'token_count' => 0]
            );
        }

        $user->notify(new TestNotification($title, $body, $data));

        return static::successResponse(
            [
                'user_id' => $userId,
                'title' => $title,
                'tokens_sent' => $tokenCount,
            ],
            'Test notification sent'
        );
    }
}
