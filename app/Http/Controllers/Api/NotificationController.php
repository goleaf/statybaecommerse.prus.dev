<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NotificationIndexRequest;
use App\Http\Requests\Api\NotificationMutationRequest;
use App\Http\Requests\Api\NotificationSearchRequest;
use App\Http\Requests\Api\NotificationShowRequest;
use App\Http\Requests\Api\NotificationStatsRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
        $definition = $this->notificationListDefinition();
        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $user = Auth::user();
        $notifications = $this->notificationService->getUserNotifications($user, $listQuery, $definition);

        $response = ListResponse::fromPaginator(
            $notifications,
            $listQuery,
            static fn (Notification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
                'is_read' => $notification->is_read,
                'is_urgent' => $notification->is_urgent,
            ],
        );

        return response()->json([
            'success' => true,
            'data' => $response['data'],
            'meta' => $response['meta'],
            'links' => $response['links'],
        ]);
    }

    /**
     * Handle stats functionality with proper error handling.
     */
    public function stats(NotificationStatsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(NotificationMutationRequest $request, Notification $notification): JsonResponse
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
     */
    public function markAsUnread(NotificationMutationRequest $request, Notification $notification): JsonResponse
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
     */
    public function markAllAsRead(NotificationMutationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsReadForUser($user);

        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as read", 'count' => $count]);
    }

    /**
     * Handle markAllAsUnread functionality with proper error handling.
     */
    public function markAllAsUnread(NotificationMutationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsUnreadForUser($user);

        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as unread", 'count' => $count]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(NotificationShowRequest $request, Notification $notification): JsonResponse
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
     */
    public function destroy(NotificationMutationRequest $request, Notification $notification): JsonResponse
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
     */
    public function search(NotificationSearchRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $query = $validated['q'];
        $type = $validated['type'] ?? null;
        $read = array_key_exists('read', $validated) ? (bool) $validated['read'] : null;
        $perPage = (int) ($validated['per_page'] ?? 25);
        $notifications = $this->notificationService->searchNotifications($query, $user, $type, $read, $perPage);

        return response()->json(['success' => true, 'data' => $notifications->items(), 'pagination' => ['current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage(), 'per_page' => $notifications->perPage(), 'total' => $notifications->total(), 'from' => $notifications->firstItem(), 'to' => $notifications->lastItem()]]);
}

    private function notificationListDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(25)
            ->maxPerPage(100)
            ->defaultSort('created_at', 'desc')
            ->allowedSorts([
                'created_at' => ['column' => 'created_at'],
                'read_at' => ['column' => 'read_at'],
                'type' => ['column' => 'type'],
            ])
            ->filters([
                'type' => ['type' => 'string', 'nullable' => true, 'scope' => 'byType'],
                'read' => [
                    'type' => 'bool',
                    'nullable' => true,
                    'callback' => static function (Builder $query, bool $read): void {
                        $read ? $query->read() : $query->unread();
                    },
                ],
                'urgent' => [
                    'type' => 'bool',
                    'nullable' => true,
                    'callback' => static function (Builder $query, bool $urgent): void {
                        $urgent ? $query->urgent() : $query->normal();
                    },
                ],
            ]);
    }
}
