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
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

final class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $input = array_merge($request->query(), $request->validated());
        $listQuery = ListQueryValidator::fromArray($input, $this->notificationListDefinition());
        $notifications = $this->notificationService->getUserNotifications($user, $listQuery);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => ListResponse::meta($listQuery, $notifications),
            'links' => ListResponse::links($notifications),
        ]);
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
        $input = array_merge($request->query(), $request->validated());
        $listQuery = ListQueryValidator::fromArray($input, $this->notificationListDefinition(includeSearch: true));
        $notifications = $this->notificationService->searchNotifications($user, $listQuery);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => ListResponse::meta($listQuery, $notifications),
            'links' => ListResponse::links($notifications),
        ]);
    }

    private function notificationListDefinition(bool $includeSearch = false): ListQueryDefinition
    {
        $filters = [
            'type' => [
                'type' => 'string',
                'callback' => static function (Builder $builder, string $type): void {
                    $builder->byType($type);
                },
            ],
            'read' => [
                'type' => 'bool',
                'nullable' => true,
                'callback' => static function (Builder $builder, bool $read): void {
                    $read ? $builder->read() : $builder->unread();
                },
            ],
        ];

        if ($includeSearch) {
            $filters['q'] = [
                'type' => 'string',
                'callback' => static function (Builder $builder, string $term): void {
                    $builder->where(function (Builder $query) use ($term): void {
                        $query->where('data->title', 'like', '%'.$term.'%')
                            ->orWhere('data->message', 'like', '%'.$term.'%')
                            ->orWhere('data->type', 'like', '%'.$term.'%');
                    });
                },
            ];
        }

        return new ListQueryDefinition(
            filters: $filters,
            sortable: [
                'created_at' => ['column' => 'notifications.created_at', 'default_direction' => 'desc'],
                'type' => ['column' => 'notifications.type'],
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            maxPerPage: 100,
        );
    }
}
