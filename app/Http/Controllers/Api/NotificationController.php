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

final class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $user = Auth::user();

        $definition = ListQueryDefinition::make(
            allowedSorts: [
                'created_at' => 'created_at',
                'read_at' => 'read_at',
                'type' => 'type',
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);
        $filters = $listQuery->filters;

        $type = $filters['type'] ?? null;
        $read = array_key_exists('read', $filters)
            ? filter_var($filters['read'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $notifications = $this->notificationService->getUserNotifications(
            $user,
            $listQuery->perPage,
            $type,
            $read,
            $definition->resolveSortColumn($listQuery->sortField),
            $listQuery->sortDirection,
            $listQuery->page,
        )->appends($request->query());

        $response = ListResponse::fromPaginator(
            $notifications->through(static function (Notification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            }),
        );

        return response()->json($response);
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
        $searchQuery = $request->get('q');

        if (empty($searchQuery)) {
            return response()->json(['success' => false, 'message' => 'Search query is required'], 400);
        }

        $definition = ListQueryDefinition::make(
            allowedSorts: [
                'created_at' => 'created_at',
                'read_at' => 'read_at',
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);
        $filters = $listQuery->filters;

        $builder = Notification::forUser($user->id);

        if ($type = $filters['type'] ?? null) {
            $builder->byType($type);
        }

        if (array_key_exists('read', $filters)) {
            $isRead = filter_var($filters['read'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isRead === true) {
                $builder->read();
            } elseif ($isRead === false) {
                $builder->unread();
            }
        }

        $builder->where(function ($q) use ($searchQuery) {
            $q->where('data->title', 'like', "%{$searchQuery}%")
                ->orWhere('data->message', 'like', "%{$searchQuery}%")
                ->orWhere('type', 'like', "%{$searchQuery}%");
        });

        $builder = $listQuery->apply($builder, $definition);

        $notifications = $builder->paginate($listQuery->perPage, ['*'], 'page', $listQuery->page)
            ->appends($request->query());

        $response = ListResponse::fromPaginator(
            $notifications->through(static function (Notification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            }),
        );

        $response['context'] = ['query' => $searchQuery];

        return response()->json($response);
    }
}
