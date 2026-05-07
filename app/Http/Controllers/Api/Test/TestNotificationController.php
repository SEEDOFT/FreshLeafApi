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
            Log::warning('TestNotification: User not found', ['user_id' => $userId]);

            return static::errorResponse(
                'User not found',
                404,
                ['user_id' => $userId]
            );
        }

        $fcmTokens = $user->routeNotificationForFcm();
        $tokenCount = count($fcmTokens);

        Log::info('TestNotification: FCM tokens retrieved', [
            'user_id' => $userId,
            'token_count' => $tokenCount,
            'tokens' => $fcmTokens,
        ]);

        if ($tokenCount === 0) {
            Log::warning('TestNotification: No active devices for user', [
                'user_id' => $userId,
            ]);

            return static::errorResponse(
                'User has no registered devices',
                400,
                ['user_id' => $userId, 'token_count' => 0]
            );
        }

        Log::info('TestNotification: About to send notification', [
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $user->notify(new TestNotification($title, $body, $data));

        Log::info('TestNotification: Notification sent successfully', [
            'user_id' => $userId,
            'tokens_sent' => $tokenCount,
        ]);

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
