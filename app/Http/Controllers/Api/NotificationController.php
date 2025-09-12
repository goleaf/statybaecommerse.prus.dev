<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $perPage = (int) $request->get('per_page', 25);
        $type = $request->get('type');
        $read = $request->get('read') !== null ? (bool) $request->get('read') : null;

        $notifications = $this->notificationService->getUserNotifications($user, $perPage, $type, $read);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ]);
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $this->notificationService->markAsUnread($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsReadForUser($user);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'count' => $count,
        ]);
    }

    /**
     * Mark all notifications as unread
     */
    public function markAllAsUnread(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsUnreadForUser($user);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as unread",
            'count' => $count,
        ]);
    }

    /**
     * Get a specific notification
     */
    public function show(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Search notifications
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $request->get('q');
        $type = $request->get('type');
        $read = $request->get('read') !== null ? (bool) $request->get('read') : null;
        $perPage = (int) $request->get('per_page', 25);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
            ], 400);
        }

        $notifications = $this->notificationService->searchNotifications($query, $user, $type, $read, $perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ]);
    }
}
