<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Notifications\NotificationFilterData;
use App\Data\Notifications\NotificationPageData;
use App\Data\Notifications\NotificationPaginationData;
use App\Data\Notifications\NotificationPayloadData;
use App\Data\Notifications\NotificationSearchParametersData;
use App\Data\Notifications\NotificationStatsData;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class NotificationService
{
    public function createNotification(
        Model $notifiable,
        string $type,
        array $data = [],
        bool $urgent = false,
        ?string $color = null,
        array $tags = []
    ): Notification {
        $notificationData = array_merge(
            [
                'type' => $type,
                'urgent' => $urgent,
                'color' => $color,
                'tags' => $tags,
            ],
            $data,
        );

        return Notification::create([
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'data' => $notificationData,
        ]);
    }

    public function createOrderNotification(User $user, string $action, array $orderData = [], bool $urgent = false): Notification
    {
        $data = [
            'title' => __('notifications.order.'.$action),
            'message' => $this->getOrderMessage($action, $orderData),
            'type' => 'order',
            'order_id' => $orderData['id'] ?? null,
            'order_number' => $orderData['number'] ?? null,
        ];

        return $this->createNotification($user, 'App\\Notifications\\OrderNotification', $data, $urgent);
    }

    public function createProductNotification(User $user, string $action, array $productData = [], bool $urgent = false): Notification
    {
        $data = [
            'title' => __('notifications.product.'.$action),
            'message' => $this->getProductMessage($action, $productData),
            'type' => 'product',
            'product_id' => $productData['id'] ?? null,
            'product_name' => $productData['name'] ?? null,
        ];

        return $this->createNotification($user, 'App\\Notifications\\ProductNotification', $data, $urgent);
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

    public function markAllAsReadForUser(User $user): int
    {
        return Notification::markAllAsReadForUser($user->id);
    }

    /**
     * Handle getUserNotifications functionality with proper error handling.
     *
     * @return Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserNotifications(User $user, int $perPage = 25, ?string $type = null, ?bool $read = null): LengthAwarePaginator
    {
        $query = $this->applyFilters(Notification::forUser($user->id), $type, $read);

        return $query->latest()->paginate($perPage);
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
    public function searchNotifications(string $query, User $user, ?string $type = null, ?bool $read = null, int $perPage = 25): LengthAwarePaginator
    {
        $builder = $this->applyFilters(Notification::forUser($user->id), $type, $read)
            ->where(function (Builder $searchQuery) use ($query): void {
                $searchQuery->where('data->title', 'like', '%'.$query.'%')
                    ->orWhere('data->message', 'like', '%'.$query.'%')
                    ->orWhere('data->type', 'like', '%'.$query.'%');
            });

        return $builder->latest()->paginate($perPage);
    }

    private function applyFilters(Builder $query, ?string $type, ?bool $read): Builder
    {
        if ($type) {
            $query->byType($type);
        }

        if ($read !== null) {
            $query = $read ? $query->read() : $query->unread();
        }

        return $query;
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
}
