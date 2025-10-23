<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPaginationData;
use App\Data\Notifications\NotificationPayloadData;
use App\Data\Notifications\NotificationSearchParametersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiRequest;
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

    public function index(NotificationIndexRequest $request): JsonResponse
    {
        $user = Auth::user();
        $input = $request->validated();
        $filters = NotificationFilterData::fromArray($input);
        $pagination = NotificationPaginationData::fromArray($input);

        $page = $this->notificationService->getUserNotifications($user, $filters, $pagination);

        return response()->json([
            'success' => true,
            'data'    => array_map(static fn (NotificationPayloadData $payload): array => $payload->toArray(), $page->items()),
            'meta'    => $page->meta(),
            'links'   => $page->links(),
        ]);
    }

    public function stats(NotificationStatsRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $stats = $this->notificationService->getUserNotificationStats($user);

        return response()->json([
            'success' => true,
            'data'    => $stats->toArray(),
        ]);
    }

    public function markAsRead(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        try {
            $payload = $this->notificationService->markAsReadForUser($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        // Return only the normalized payload to ensure the public contract stays minimal.
        return response()->json([
            'success' => true,
            'data'    => $payload->toArray(),
        ]);
    }

    public function markAsUnread(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        try {
            $payload = $this->notificationService->markAsUnreadForUser($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        // Provide the refreshed payload without auxiliary message keys for parity with tests.
        return response()->json([
            'success' => true,
            'data'    => $payload->toArray(),
        ]);
    }

    public function markAllAsRead(NotificationMutationRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $count = $this->notificationService->markAllAsReadForUser($user);

        // Keep the response lean by exposing the affected count directly.
        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    public function markAllAsUnread(NotificationMutationRequest $request): JsonResponse
    {
        $user = $this->requireUser($request);
        $count = $this->notificationService->markAllAsUnreadForUser($user);

        // Mirror the mark-all-read response shape to avoid ambiguous messaging fields.
        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

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

    public function destroy(NotificationMutationRequest $request, Notification $notification): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->notificationService->deleteForUser($user, $notification);
        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        }

        // Acknowledge deletion success without redundant message payloads.
        return response()->json([
            'success' => true,
        ]);
    }

    public function search(NotificationSearchRequest $request): JsonResponse
    {
        $user = Auth::user();
        $input = $request->validated();
        $pagination = NotificationPaginationData::fromArray($input);
        $search = NotificationSearchParametersData::fromArray($input);

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
