<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use App\Support\ListQuery\ListResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): JsonResponse
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
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Handle markAsRead functionality with proper error handling.
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
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsReadForUser($user);

        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as read", 'count' => $count]);
    }

    /**
     * Handle markAllAsUnread functionality with proper error handling.
     */
    public function markAllAsUnread(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsUnreadForUser($user);

        return response()->json(['success' => true, 'message' => "Marked {$count} notifications as unread", 'count' => $count]);
    }

    /**
     * Display the specified resource with related data.
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
     */
    public function search(Request $request): JsonResponse
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
