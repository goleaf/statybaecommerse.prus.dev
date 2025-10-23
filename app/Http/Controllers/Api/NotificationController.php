<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPaginationData;
use App\Data\Notifications\NotificationPayloadData;
use App\Data\Notifications\NotificationSearchParametersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NotificationIndexRequest;
use App\Http\Requests\Api\NotificationMutationRequest;
use App\Http\Requests\Api\NotificationSearchRequest;
use App\Http\Requests\Api\NotificationShowRequest;
use App\Http\Requests\Api\NotificationStatsRequest;
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
    public function index(NotificationIndexRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 25);
        $type = $validated['type'] ?? null;
        $read = array_key_exists('read', $validated) ? (bool) $validated['read'] : null;
        $notifications = $this->notificationService->getUserNotifications($user, $perPage, $type, $read);

        $page = $this->notificationService->getUserNotifications($user, $filters, $pagination);

        return response()->json([
            'success' => true,
            'data'    => array_map(static fn (NotificationPayloadData $payload): array => $payload->toArray(), $page->items()),
            'meta'    => $page->meta(),
            'links'   => $page->links(),
        ]);
    }

    /**
     * Handle stats functionality with proper error handling.
     */
    public function stats(NotificationStatsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json([
            'success' => true,
            'data'    => $stats->toArray(),
        ]);
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

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
        $user = Auth::user();

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
        $user = Auth::user();
        $count = $this->notificationService->markAllAsReadForUser($user);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'count'   => $count,
        ]);
    }

    /**
     * Handle markAllAsUnread functionality with proper error handling.
     */
    public function markAllAsUnread(NotificationMutationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsUnreadForUser($user);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as unread",
            'count'   => $count,
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(NotificationShowRequest $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        try {
            $payload = $this->notificationService->show($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'data'    => $payload->toArray(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

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
    public function search(NotificationSearchRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $query = $validated['q'];
        $type = $validated['type'] ?? null;
        $read = array_key_exists('read', $validated) ? (bool) $validated['read'] : null;
        $perPage = (int) ($validated['per_page'] ?? 25);
        $notifications = $this->notificationService->searchNotifications($query, $user, $type, $read, $perPage);

        $page = $this->notificationService->searchNotifications($user, $search, $pagination);

        return response()->json([
            'success' => true,
            'data'    => array_map(static fn (NotificationPayloadData $payload): array => $payload->toArray(), $page->items()),
            'meta'    => $page->meta(),
            'links'   => $page->links(),
        ]);
    }

    private function notFoundResponse(): JsonResponse
    {
        // Normalize missing resource responses to the shared problem+json structure.
        return ApiErrorResponse::problem(
            request: request(),
            errorCode: ErrorCodes::NOT_FOUND,
            detail: __('notifications.errors.not_found'),
            status: 404,
            title: ApiErrorResponse::titleFor(ErrorCodes::NOT_FOUND),
        );
    }
}
