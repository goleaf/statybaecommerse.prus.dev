<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\DTOs\Notifications\NotificationCollectionData;
use App\Application\DTOs\Notifications\NotificationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListNotificationsRequest;
use App\Http\Requests\Api\SearchNotificationsRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        $collection = $this->notificationService->getUserNotifications($user, $request->toDto());

        return $this->collectionResponse($collection);
    }

    /**
     * Handle stats functionality with proper error handling.
     */
    public function stats(NotificationStatsRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json(['success' => true, 'data' => $stats->toArray()]);
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->authenticatedUser();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $this->notificationService->markAsRead($notification);

        try {
            $payload = $this->notificationService->markAsReadForUser($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data'    => $payload->toArray(),
        ]);
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->authenticatedUser();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $this->notificationService->markAsUnread($notification);

        try {
            $payload = $this->notificationService->markAsUnreadForUser($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
            'data'    => $payload->toArray(),
        ]);
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(NotificationMutationRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        $result = $this->notificationService->markAllAsReadForUser($user);

        return response()->json([
            'success' => true,
            'message' => sprintf('Marked %d notifications as read', $result->count()),
            'count' => $result->count(),
        ]);
    }

    /**
     * Handle markAllAsUnread functionality with proper error handling.
     */
    public function markAllAsUnread(NotificationMutationRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        $result = $this->notificationService->markAllAsUnreadForUser($user);

        return response()->json([
            'success' => true,
            'message' => sprintf('Marked %d notifications as unread', $result->count()),
            'count' => $result->count(),
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(NotificationShowRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->authenticatedUser();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $resource = NotificationData::fromModel($notification);

        return response()->json(['success' => true, 'data' => $resource->toArray()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->authenticatedUser();
        // Ensure the notification belongs to the authenticated user
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $notification->delete();

        try {
            $this->notificationService->deleteForUser($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(SearchNotificationsRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser();
        $collection = $this->notificationService->searchNotifications($user, $request->toDto());

        return $this->collectionResponse($collection);
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(401, 'Unauthenticated.');
        }

        return $user;
    }

    private function collectionResponse(NotificationCollectionData $collection): JsonResponse
    {
        $payload = $collection->toArray();

        return response()->json([
            'success' => true,
            'data' => $payload['data'],
            'pagination' => $payload['pagination'],
        ]);
    }
}
