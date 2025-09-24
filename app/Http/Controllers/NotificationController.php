<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
    public function index()
    {
        return view('notifications.index');
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);
        if (! $notification || $notification->notifiable_id !== Auth::id()) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => __('Notification marked as read')]);
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);
        if (! $notification || $notification->notifiable_id !== Auth::id()) {
            return response()->json(['error' => 'Notification not found'], 404);
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
    public function delete(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);
        if (! $notification || $notification->notifiable_id !== Auth::id()) {
            return response()->json(['error' => 'Notification not found'], 404);
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
        $notifications = Auth::user()->notifications()->latest()->limit(5)->get()->map(function ($notification) {
            return ['id' => $notification->id, 'type' => class_basename($notification->type), 'title' => $notification->data['title'] ?? __('Notification'), 'message' => $notification->data['message'] ?? '', 'read_at' => $notification->read_at, 'created_at' => $notification->created_at->diffForHumans()];
        });

        return response()->json(['notifications' => $notifications]);
    }
}
