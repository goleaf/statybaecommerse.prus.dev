<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

/**
 * NotificationController
 *
 * HTTP controller handling NotificationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class NotificationController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(): View|RedirectResponse
    {
        if (! Auth::check()) {
            // Redirect guests to the login screen instead of rendering the notification view.
            return redirect()->guest('/login');
        }

        return view('notifications.index');
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(DatabaseNotification $notification): JsonResponse
    {
        $guardResponse = $this->ensureNotificationBelongsToUser($notification);

        if ($guardResponse instanceof JsonResponse) {
            return $guardResponse;
        }

        // Mark the notification as read in a single call to avoid refreshing the model twice.
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => __('Notification marked as read')]);
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(DatabaseNotification $notification): JsonResponse
    {
        $guardResponse = $this->ensureNotificationBelongsToUser($notification);

        if ($guardResponse instanceof JsonResponse) {
            return $guardResponse;
        }

        // Reset the notification read timestamp to indicate it requires user attention again.
        $notification->markAsUnread();

        return response()->json(['success' => true, 'message' => __('Notification marked as unread')]);
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (! $user instanceof AuthenticatableContract) {
            return $this->unauthenticatedResponse();
        }

        // Update unread notifications in bulk to avoid loading the entire collection into memory.
        $this->notificationQueryForUser($user)
            ->whereNull('read_at')
            ->update(['read_at' => Date::now()]);

        return response()->json(['success' => true, 'message' => __('All notifications marked as read')]);
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(DatabaseNotification $notification): JsonResponse
    {
        $guardResponse = $this->ensureNotificationBelongsToUser($notification);

        if ($guardResponse instanceof JsonResponse) {
            return $guardResponse;
        }

        // Remove the notification once ownership has been validated.
        $notification->delete();

        return response()->json(['success' => true, 'message' => __('Notification deleted')]);
    }

    /**
     * Handle clearAll functionality with proper error handling.
     */
    public function clearAll(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (! $user instanceof AuthenticatableContract) {
            return $this->unauthenticatedResponse();
        }

        // Delete notifications via a scoped query to avoid loading the collection into memory.
        $this->notificationQueryForUser($user)->delete();

        return response()->json(['success' => true, 'message' => __('All notifications cleared')]);
    }

    /**
     * Handle getUnreadCount functionality with proper error handling.
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (! $user instanceof AuthenticatableContract) {
            return $this->unauthenticatedResponse();
        }

        // Query the unread notifications count without hydrating the entire collection for efficiency.
        $count = $this->notificationQueryForUser($user)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Handle getRecent functionality with proper error handling.
     */
    public function getRecent(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (! $user instanceof AuthenticatableContract) {
            return $this->unauthenticatedResponse();
        }

        // Retrieve only the latest five notifications and map them into a lightweight payload for the UI.
        /**
         * @var \Illuminate\Database\Eloquent\Collection<int, DatabaseNotification> $recentNotifications
         */
        $recentNotifications = $this->notificationQueryForUser($user)
            ->latest()
            ->limit(5)
            ->get();

        $notifications = $recentNotifications->map(
            static fn (DatabaseNotification $notification): array => [
                'id'         => (string) $notification->id,
                'type'       => class_basename($notification->type),
                'title'      => $notification->data['title'] ?? __('Notification'),
                'message'    => $notification->data['message'] ?? '',
                'read_at'    => $notification->read_at,
                'created_at' => $notification->created_at?->diffForHumans(),
            ]
        );

        return response()->json(['notifications' => $notifications]);
    }

    private function ensureNotificationBelongsToUser(DatabaseNotification $notification): ?JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (! $user instanceof AuthenticatableContract) {
            return $this->notificationNotFoundResponse();
        }

        // Convert potential morph-map aliases into the underlying class name for comparison.
        $notifiableClass = Relation::getMorphedModel($notification->notifiable_type) ?? $notification->notifiable_type;

        $userIdentifier = $this->resolveUserIdentifier($user);

        if ($userIdentifier === null) {
            return $this->notificationNotFoundResponse();
        }

        if ((string) $notification->notifiable_id !== $userIdentifier) {
            return $this->notificationNotFoundResponse();
        }

        $expectedTypes = [$user::class, $user->getMorphClass()];

        if (! in_array($notification->notifiable_type, $expectedTypes, true) && ! in_array($notifiableClass, $expectedTypes, true)) {
            return $this->notificationNotFoundResponse();
        }

        return null;
    }

    private function notificationNotFoundResponse(): JsonResponse
    {
        return response()->json(['error' => 'Notification not found'], 404);
    }

    private function getAuthenticatedUser(): ?AuthenticatableContract
    {
        // Resolve the currently authenticated user using the default guard configured for the web layer.
        return Auth::user();
    }

    /**
     * @return Builder<DatabaseNotification>
     */
    private function notificationQueryForUser(AuthenticatableContract $user): Builder
    {
        // Scope notifications to the authenticated user identifier and morph class for consistent querying.
        $userIdentifier = $this->resolveUserIdentifier($user);

        return DatabaseNotification::query()
            ->where('notifiable_id', $userIdentifier)
            ->where('notifiable_type', $user->getMorphClass());
    }

    private function resolveUserIdentifier(AuthenticatableContract $user): ?string
    {
        // Normalize the user identifier to a string for reliable comparison across database drivers.
        $userIdentifier = $user->getAuthIdentifier();

        if (is_string($userIdentifier) || is_int($userIdentifier)) {
            return (string) $userIdentifier;
        }

        return null;
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        // Provide a consistent JSON structure for unauthenticated access attempts.
        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}
