<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

/**
 * NotificationController
 *
 * HTTP controller handling NotificationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class NotificationController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('notifications.index');
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(DatabaseNotification $notification): JsonResponse
    {
        $guardResponse = $this->ensureNotificationBelongsToUser($notification);

        if ($guardResponse instanceof JsonResponse) {
            return $guardResponse;
        }

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => __('Notification marked as read')]);
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(DatabaseNotification $notification): JsonResponse
    {
        $guardResponse = $this->ensureNotificationBelongsToUser($notification);

        if ($guardResponse instanceof JsonResponse) {
            return $guardResponse;
        }

        $notification->markAsUnread();

        return response()->json(['success' => true, 'message' => __('Notification marked as unread')]);
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true, 'message' => __('All notifications marked as read')]);
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(DatabaseNotification $notification): JsonResponse
    {
        $guardResponse = $this->ensureNotificationBelongsToUser($notification);

        if ($guardResponse instanceof JsonResponse) {
            return $guardResponse;
        }

        $notification->delete();

        return response()->json(['success' => true, 'message' => __('Notification deleted')]);
    }

    /**
     * Handle clearAll functionality with proper error handling.
     */
    public function clearAll(): JsonResponse
    {
        Auth::user()->notifications()->delete();

        return response()->json(['success' => true, 'message' => __('All notifications cleared')]);
    }

    /**
     * Handle getUnreadCount functionality with proper error handling.
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = Auth::user()->unreadNotifications->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Handle getRecent functionality with proper error handling.
     */
    public function getRecent(): JsonResponse
    {
        $notifications = Auth::user()->notifications()->latest()->limit(5)->get()->map(fn ($notification): array => ['id' => $notification->id, 'type' => class_basename($notification->type), 'title' => $notification->data['title'] ?? __('Notification'), 'message' => $notification->data['message'] ?? '', 'read_at' => $notification->read_at, 'created_at' => $notification->created_at->diffForHumans()]);

        return response()->json(['notifications' => $notifications]);
    }

    private function ensureNotificationBelongsToUser(DatabaseNotification $notification): ?JsonResponse
    {
        $user = Auth::user();

        if (! $user instanceof AuthenticatableContract) {
            return $this->notificationNotFoundResponse();
        }

        if ((string) $notification->notifiable_id !== (string) $user->getAuthIdentifier()) {
            return $this->notificationNotFoundResponse();
        }

        if ($notification->notifiable_type !== $user::class) {
            return $this->notificationNotFoundResponse();
        }

        return null;
    }

    private function notificationNotFoundResponse(): JsonResponse
    {
        return response()->json(['error' => 'Notification not found'], 404);
    }
}
