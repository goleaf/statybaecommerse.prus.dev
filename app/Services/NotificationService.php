<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPaginationData;
use App\Data\Notifications\NotificationPaginationOptions;
use App\Data\Notifications\NotificationPayloadData;
use App\Data\Notifications\NotificationSearchParameters;
use App\Data\Notifications\NotificationStatsData;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * NotificationService
 *
 * Service class containing NotificationService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class NotificationService
{
    /**
     * Persist a notification for the given notifiable model.
     */
    public function createNotification(Model $notifiable, NotificationPayloadData $payload): NotificationPayloadData
    {
        $notification = Notification::create([
            'type' => $payload->notificationClass,
            'notifiable_type' => $notifiable::class,
            'notifiable_id' => $notifiable->getKey(),
            'data' => $payload->toStoredData(),
        ]);

        return NotificationPayloadData::fromModel($notification);
    }

    /**
     * Create an order notification with translated content and metadata.
     */
    public function createOrderNotification(User $user, string $action, array $orderData = [], bool $urgent = false): NotificationPayloadData
    {
        $payload = NotificationPayloadData::make(
            'App\\Notifications\\OrderNotification',
            'order',
            __('notifications.order.'.$action),
            $this->getOrderMessage($action, $orderData),
            $urgent,
            context: array_merge(
                ['action' => $action],
                array_filter([
                    'order_id' => $orderData['id'] ?? null,
                    'order_number' => $orderData['number'] ?? null,
                ], static fn ($value) => $value !== null),
            ),
        );

        return $this->createNotification($user, $payload);
    }

    /**
     * Create a product notification with translated content and metadata.
     */
    public function createProductNotification(User $user, string $action, array $productData = [], bool $urgent = false): NotificationPayloadData
    {
        $payload = NotificationPayloadData::make(
            'App\\Notifications\\ProductNotification',
            'product',
            __('notifications.product.'.$action),
            $this->getProductMessage($action, $productData),
            $urgent,
            context: array_merge(
                ['action' => $action],
                array_filter([
                    'product_id' => $productData['id'] ?? null,
                    'product_name' => $productData['name'] ?? null,
                ], static fn ($value) => $value !== null),
            ),
        );

        return $this->createNotification($user, $payload);
    }

    /**
     * Mark a notification as read and return the updated payload representation.
     */
    public function markAsRead(Notification $notification): NotificationPayloadData
    {
        $notification->markAsRead();

        return NotificationPayloadData::fromModel($notification->fresh());
    }

    /**
     * Mark a notification as unread and return the updated payload representation.
     */
    public function markAsUnread(Notification $notification): NotificationPayloadData
    {
        $notification->markAsUnread();

        return NotificationPayloadData::fromModel($notification->fresh());
    }

    /**
     * Mark all notifications as read for the given user.
     */
    public function markAllAsReadForUser(User $user): int
    {
        return Notification::markAllAsReadForUser($user->id);
    }

    /**
     * Aggregate paginated notifications for a user using the provided filters.
     */
    public function getUserNotifications(User $user, NotificationFilterData $filters, NotificationPaginationOptions $pagination): NotificationPaginationData
    {
        $query = $this->applyFilters(Notification::forUser($user->id), $filters);

        return NotificationPaginationData::fromPaginator($query->latest()->paginate($pagination->perPage));
    }

    /**
     * Aggregate statistics for the authenticated user's notifications.
     */
    public function getUserNotificationStats(User $user): NotificationStatsData
    {
        $baseQuery = Notification::forUser($user->id);

        return new NotificationStatsData(
            (clone $baseQuery)->count(),
            (clone $baseQuery)->read()->count(),
            (clone $baseQuery)->unread()->count(),
            (clone $baseQuery)->urgent()->count(),
        );
    }

    /**
     * Mark all notifications as unread for the given user.
     */
    public function markAllAsUnreadForUser(User $user): int
    {
        return Notification::markAllAsUnreadForUser($user->id);
    }

    /**
     * Search notifications for the authenticated user using the provided parameters.
     */
    public function searchNotifications(User $user, NotificationSearchParameters $parameters): NotificationPaginationData
    {
        $builder = $this->applyFilters(Notification::forUser($user->id), $parameters->filters)
            ->where(function (Builder $searchQuery) use ($parameters): void {
                $searchQuery->where('data->title', 'like', '%'.$parameters->query.'%')
                    ->orWhere('data->message', 'like', '%'.$parameters->query.'%')
                    ->orWhere('data->type', 'like', '%'.$parameters->query.'%');
            });

        return NotificationPaginationData::fromPaginator(
            $builder->latest()->paginate($parameters->pagination->perPage),
        );
    }

    private function applyFilters(Builder $query, NotificationFilterData $filters): Builder
    {
        if ($filters->type) {
            $query->byType($filters->type);
        }

        if ($filters->read !== null) {
            $query = $filters->read ? $query->read() : $query->unread();
        }

        return $query;
    }

    /**
     * Handle getOrderMessage functionality with proper error handling.
     */
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

    /**
     * Handle getProductMessage functionality with proper error handling.
     */
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
}
