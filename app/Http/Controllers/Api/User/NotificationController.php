<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\User\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * Retrieve the authenticated user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->with(['type', 'status'])
            ->latest()
            ->paginate();

        return static::successResponse(
            message: __('api.notifications.retrieved'),
            data: NotificationResource::collection($notifications)->response()->getData(true)
        );
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return static::errorResponse(
                message: __('api.notifications.not_found'),
                code: 404
            );
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => Carbon::now()]);
        }

        return static::successResponse(
            message: __('api.notifications.marked_read')
        );
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return static::successResponse(
            message: __('api.notifications.all_marked_read')
        );
    }
}
