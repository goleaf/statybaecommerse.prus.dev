<?php

declare (strict_types=1);
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * NotificationController
 * 
 * HTTP controller handling NotificationController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class NotificationController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     * @param NotificationService $notificationService
     */
    public function __construct(private readonly NotificationService $notificationService)
    {
    }
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = (int) $request->get('per_page', 25);
        $type = $request->get('type');
        $read = $request->get('read') !== null ? (bool) $request->get('read') : null;
        $notifications = $this->notificationService->getUserNotifications($user, $perPage, $type, $read);
        return response()->json(['success' => true, 'data' => $notifications->items(), 'pagination' => ['current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage(), 'per_page' => $notifications->perPage(), 'total' => $notifications->total(), 'from' => $notifications->firstItem(), 'to' => $notifications->lastItem()]]);
    }
    /**
     * Handle stats functionality with proper error handling.
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->notificationService->getUserNotificationStats($user);
        return response()->json(['success' => true, 'data' => $stats]);
    }
    /**
     * Handle markAsRead functionality with proper error handling.
     * @param Notification $notification
     * @return JsonResponse
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = Auth::user();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $this->notificationService->markAsRead($notification);
        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    }
    /**
     * Handle markAsUnread functionality with proper error handling.
     * @param Notification $notification
     * @return JsonResponse
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        $user = Auth::user();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $this->notificationService->markAsUnread($notification);
        return response()->json(['success' => true, 'message' => 'Notification marked as unread']);
    }
    /**
     * Handle markAllAsRead functionality with proper error handling.
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsReadForUser($user);
        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as read", 'count' => $count]);
    }
    /**
     * Handle markAllAsUnread functionality with proper error handling.
     * @return JsonResponse
     */
    public function markAllAsUnread(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsUnreadForUser($user);
        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as unread", 'count' => $count]);
    }
    /**
     * Display the specified resource with related data.
     * @param Notification $notification
     * @return JsonResponse
     */
    public function show(Notification $notification): JsonResponse
    {
        $user = Auth::user();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $notification]);
    }
    /**
     * Remove the specified resource from storage.
     * @param Notification $notification
     * @return JsonResponse
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $user = Auth::user();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $notification->delete();
        return response()->json(['success' => true, 'message' => 'Notification deleted']);
    }
    /**
     * Handle search functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $request->get('q');
        $type = $request->get('type');
        $read = $request->get('read') !== null ? (bool) $request->get('read') : null;
        $perPage = (int) $request->get('per_page', 25);
        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Search query is required'], 400);
        }
        $notifications = $this->notificationService->searchNotifications($query, $user, $type, $read, $perPage);
        return response()->json(['success' => true, 'data' => $notifications->items(), 'pagination' => ['current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage(), 'per_page' => $notifications->perPage(), 'total' => $notifications->total(), 'from' => $notifications->firstItem(), 'to' => $notifications->lastItem()]]);
    }
}