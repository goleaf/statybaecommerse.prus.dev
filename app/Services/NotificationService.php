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

    public function markAllAsUnreadForUser(User $user): int
    {
        return Notification::markAllAsUnreadForUser($user->id);
    }

    public function getUserNotifications(
        User $user,
        NotificationFilterData $filters,
        NotificationPaginationData $pagination
    ): NotificationPageData {
        $query = $this->baseQueryForUser($user);
        $filters->apply($query);
        $pagination->apply($query);

        $paginator = $this->paginate($query, $pagination);
        $items = array_map(
            static fn (Notification $notification): NotificationPayloadData => NotificationPayloadData::fromModel($notification),
            $paginator->items(),
        );

        return NotificationPageData::fromPaginator($paginator, $pagination, $filters, null, $items);
    }

    public function searchNotifications(
        User $user,
        NotificationSearchParametersData $search,
        NotificationPaginationData $pagination
    ): NotificationPageData {
        $query = $this->baseQueryForUser($user);
        $search->apply($query);
        $pagination->apply($query);

        $paginator = $this->paginate($query, $pagination);
        $items = array_map(
            static fn (Notification $notification): NotificationPayloadData => NotificationPayloadData::fromModel($notification),
            $paginator->items(),
        );

        return NotificationPageData::fromPaginator($paginator, $pagination, $search->filters(), $search, $items);
    }

    public function getUserNotificationStats(User $user): NotificationStatsData
    {
        $baseQuery = $this->baseQueryForUser($user);

        return NotificationStatsData::fromCounts(
            (clone $baseQuery)->count(),
            (clone $baseQuery)->read()->count(),
            (clone $baseQuery)->unread()->count(),
            (clone $baseQuery)->urgent()->count(),
        );
    }

    public function show(User $user, Notification $notification): NotificationPayloadData
    {
        $this->guardNotificationOwnership($notification, $user);

        return NotificationPayloadData::fromModel($notification);
    }

    private function baseQueryForUser(User $user): Builder
    {
        return Notification::query()->forUser($user->id)->latest('created_at');
    }

    private function paginate(Builder $builder, NotificationPaginationData $pagination): LengthAwarePaginator
    {
        return $builder->paginate(
            $pagination->perPage(),
            ['*'],
            'page',
            $pagination->page(),
        );
    }

    private function guardNotificationOwnership(Notification $notification, User $user): void
    {
        if ($notification->notifiable_id !== $user->id || $notification->notifiable_type !== User::class) {
            $exception = new ModelNotFoundException('Notification not found for the authenticated user.');
            $exception->setModel(Notification::class, [$notification->id]);

            throw $exception;
        }
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
