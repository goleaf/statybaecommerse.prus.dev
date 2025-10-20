<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Data\Notifications\NotificationPayloadData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiRequest;
use App\Http\Requests\Api\NotificationIndexRequest;
use App\Http\Requests\Api\NotificationMutationRequest;
use App\Http\Requests\Api\NotificationSearchRequest;
use App\Http\Requests\Api\NotificationShowRequest;
use App\Http\Requests\Api\NotificationStatsRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * NotificationController
 *
 * HTTP controller handling NotificationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class NotificationController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(NotificationIndexRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $result = $this->notificationService->getUserNotifications($user, $request->filters(), $request->paginationOptions());

        return response()->json(array_merge(['success' => true], $result->toArray()));
    }

    /**
     * Handle stats functionality with proper error handling.
     */
    public function stats(NotificationStatsRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json(['success' => true, 'data' => $stats->toArray()]);
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($response = $this->guardNotificationOwnership($user, $notification)) {
            return $response;
        }

        $payload = $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true, 'message' => 'Notification marked as read', 'data' => $payload->toArray()]);
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($response = $this->guardNotificationOwnership($user, $notification)) {
            return $response;
        }

        $payload = $this->notificationService->markAsUnread($notification);

        return response()->json(['success' => true, 'message' => 'Notification marked as unread', 'data' => $payload->toArray()]);
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(NotificationMutationRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $count = $this->notificationService->markAllAsReadForUser($user);

        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as read", 'count' => $count]);
    }

    /**
     * Handle markAllAsUnread functionality with proper error handling.
     */
    public function markAllAsUnread(NotificationMutationRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $count = $this->notificationService->markAllAsUnreadForUser($user);

        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as unread", 'count' => $count]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(NotificationShowRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($response = $this->guardNotificationOwnership($user, $notification)) {
            return $response;
        }

        return response()->json([
            'success' => true,
            'data' => NotificationPayloadData::fromModel($notification)->toArray(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($response = $this->guardNotificationOwnership($user, $notification)) {
            return $response;
        }

        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Notification deleted']);
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(NotificationSearchRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $result = $this->notificationService->searchNotifications($user, $request->parameters());

        return response()->json(array_merge(['success' => true], $result->toArray()));
    }

    private function guardNotificationOwnership(User $user, Notification $notification): ?JsonResponse
    {
        if ($notification->notifiable_id !== $user->getKey() || $notification->notifiable_type !== User::class) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        return null;
    }

    private function requireUser(ApiRequest $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401));
        }

        return $user;
    }
}
