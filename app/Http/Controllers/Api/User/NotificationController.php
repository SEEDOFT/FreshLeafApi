<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\NotificationResource;
use App\Models\Notification;
use App\Models\NotificationStatus;
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
        $user = $this->authenticatedUser($request);

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->with(['type', 'status'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return static::successResponse(
            NotificationResource::collection($notifications),
            __('api.notifications.retrieved')
        );
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if ($notification->user_id !== $user->id) {
            abort(404, __('api.notifications.not_found'));
        }

        if (
            $notification->notification_status_id === NotificationStatus::UNREAD_ID
            || $notification->read_at === null
        ) {
            $notification->update([
                'notification_status_id' => NotificationStatus::READ_ID,
                'read_at' => Carbon::now(),
            ]);
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
        $user = $this->authenticatedUser($request);

        Notification::where('user_id', $user->id)
            ->where('notification_status_id', NotificationStatus::UNREAD_ID)
            ->whereNull('read_at')
            ->update([
                'notification_status_id' => NotificationStatus::READ_ID,
                'read_at' => Carbon::now(),
            ]);

        return static::successResponse(
            message: __('api.notifications.all_marked_read')
        );
    }
}
