<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

final class NotificationController extends Controller
{
    public function index()
    {
        return view('notifications.index');
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);
        
        if (!$notification || $notification->notifiable_id !== Auth::id()) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => __('Notification marked as read')
        ]);
    }

    public function markAsUnread(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);
        
        if (!$notification || $notification->notifiable_id !== Auth::id()) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => __('Notification marked as unread')
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => __('All notifications marked as read')
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);
        
        if (!$notification || $notification->notifiable_id !== Auth::id()) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => __('Notification deleted')
        ]);
    }

    public function clearAll(): JsonResponse
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => __('All notifications cleared')
        ]);
    }

    public function getUnreadCount(): JsonResponse
    {
        $count = Auth::user()->unreadNotifications->count();

        return response()->json([
            'count' => $count
        ]);
    }

    public function getRecent(): JsonResponse
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'title' => $notification->data['title'] ?? __('Notification'),
                    'message' => $notification->data['message'] ?? '',
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications
        ]);
    }
}
