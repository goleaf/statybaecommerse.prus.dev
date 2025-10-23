<?php

declare(strict_types=1);

namespace App\Services;

use App\Application\DTOs\Notifications\NotificationBulkActionResult;
use App\Application\DTOs\Notifications\NotificationCollectionData;
use App\Application\DTOs\Notifications\NotificationCreateData;
use App\Application\DTOs\Notifications\NotificationFilterData;
use App\Application\DTOs\Notifications\NotificationMessageData;
use App\Application\DTOs\Notifications\NotificationSearchData;
use App\Application\DTOs\Notifications\NotificationStatsData;
use App\Models\Notification;
use App\Models\User;
use App\Support\ListQuery\ListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class NotificationService
{
    /**
     * Handle createNotification functionality with proper error handling.
     */
    public function createNotification(NotificationCreateData $data): Notification
    {
        $payload = $data->message
            ->withUrgency($data->urgent)
            ->withColor($data->color)
            ->withTags($data->tags());

        return Notification::create([
            'type' => $data->notificationClass,
            'notifiable_type' => $data->notifiable::class,
            'notifiable_id' => $data->notifiable->getKey(),
            'data' => $payload->toArray(),
        ]);
    }

    public function createOrderNotification(User $user, string $action, array $orderData = [], bool $urgent = false): Notification
    {
        $message = new NotificationMessageData('order', [
            'title' => __('notifications.order.'.$action),
            'message' => $this->getOrderMessage($action, $orderData),
            'order_id' => $orderData['id'] ?? null,
            'order_number' => $orderData['number'] ?? null,
        ]);

        return $this->createNotification(new NotificationCreateData(
            notifiable: $user,
            notificationClass: 'App\\Notifications\\OrderNotification',
            message: $message,
            urgent: $urgent,
        ));
    }

    public function createProductNotification(User $user, string $action, array $productData = [], bool $urgent = false): Notification
    {
        $message = new NotificationMessageData('product', [
            'title' => __('notifications.product.'.$action),
            'message' => $this->getProductMessage($action, $productData),
            'product_id' => $productData['id'] ?? null,
            'product_name' => $productData['name'] ?? null,
        ]);

        return $this->createNotification(new NotificationCreateData(
            notifiable: $user,
            notificationClass: 'App\\Notifications\\ProductNotification',
            message: $message,
            urgent: $urgent,
        ));
    }

    public function markAsReadForUser(User $user, Notification $notification): NotificationPayloadData
    {
        $this->guardNotificationOwnership($notification, $user);
        $notification->markAsRead();

        return NotificationPayloadData::fromModel($notification->fresh());
    }

    public function markAsUnreadForUser(User $user, Notification $notification): NotificationPayloadData
    {
        $this->guardNotificationOwnership($notification, $user);
        $notification->markAsUnread();

        return NotificationPayloadData::fromModel($notification->fresh());
    }

    public function deleteForUser(User $user, Notification $notification): void
    {
        $this->guardNotificationOwnership($notification, $user);
        $notification->delete();
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(Notification $notification): bool
    {
        return $notification->markAsUnread();
    }

    /**
     * Handle markAllAsReadForUser functionality with proper error handling.
     */
    public function markAllAsReadForUser(User $user): NotificationBulkActionResult
    {
        $count = Notification::markAllAsReadForUser($user->id);

        return new NotificationBulkActionResult($count);
    }

    /**
     * Handle markAllAsUnreadForUser functionality with proper error handling.
     */
    public function markAllAsUnreadForUser(User $user): NotificationBulkActionResult
    {
        $count = Notification::markAllAsUnreadForUser($user->id);

        return new NotificationBulkActionResult($count);
    }

    /**
     * Handle getUserNotifications functionality with proper error handling.
     */
    public function getUserNotifications(User $user, ListQuery $query): LengthAwarePaginator
    {
        $builder = Notification::forUser($user->id);

        $query->applyFilters($builder);
        $query->applySorts($builder);

        if (! $query->hasSort('created_at')) {
            $builder->latest();
        }

        return $builder->paginate($query->perPage(), ['*'], 'page', $query->page());
    }

    /**
     * Aggregate statistics for the authenticated user's notifications.
     */
    public function getUserNotificationStats(User $user): array
    {
        $baseQuery = Notification::forUser($user->id);

        return [
            'total' => (clone $baseQuery)->count(),
            'read' => (clone $baseQuery)->read()->count(),
            'unread' => (clone $baseQuery)->unread()->count(),
            'urgent' => (clone $baseQuery)->urgent()->count(),
        ];
    }

    /**
     * Mark all notifications as unread for the given user.
     */
    public function markAllAsUnreadForUser(User $user): int
    {
        return Notification::markAllAsUnreadForUser($user->id);
    }

    /**
     * Search notifications for the authenticated user.
     */
    public function searchNotifications(User $user, ListQuery $query): LengthAwarePaginator
    {
        return $this->getUserNotifications($user, $query);
    }

    private function getOrderMessage(string $action, array $orderData): string
    {
        return match ($action) {
            'created' => "Naujas užsakymas #{$orderData['number']} buvo sukurtas.",
            'updated' => "Užsakymas #{$orderData['number']} buvo atnaujintas.",
            'cancelled' => "Užsakymas #{$orderData['number']} buvo atšauktas.",
            'completed' => "Užsakymas #{$orderData['number']} buvo užbaigtas.",
            'shipped' => "Užsakymas #{$orderData['number']} buvo išsiųstas.",
            'delivered' => "Užsakymas #{$orderData['number']} buvo pristatytas.",
            default => "Užsakymas #{$orderData['number']} buvo {$action}.",
        };
    }

    private function getProductMessage(string $action, array $productData): string
    {
        return match ($action) {
            'created' => "Naujas produktas '{$productData['name']}' buvo sukurtas.",
            'updated' => "Produktas '{$productData['name']}' buvo atnaujintas.",
            'deleted' => "Produktas '{$productData['name']}' buvo ištrintas.",
            'low_stock' => "Produktas '{$productData['name']}' turi mažai atsargų.",
            'out_of_stock' => "Produktas '{$productData['name']}' baigėsi atsargos.",
            'back_in_stock' => "Produktas '{$productData['name']}' atsikūrė atsargos.",
            default => "Produktas '{$productData['name']}' buvo {$action}.",
        };
    }

    private function applyFilters(Builder $query, NotificationFilterData $filter): Builder
    {
        if ($filter->type !== null) {
            $query->byType($filter->type);
        }

        if ($filter->read !== null) {
            $filter->read ? $query->read() : $query->unread();
        }

        return $query;
    }
}
