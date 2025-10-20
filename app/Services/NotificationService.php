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
use Illuminate\Database\Eloquent\Builder;

/**
 * NotificationService
 *
 * Service class containing NotificationService business logic, external integrations, and complex operations with proper error handling and logging.
 */
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

    /**
     * Handle createOrderNotification functionality with proper error handling.
     */
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

    /**
     * Handle createProductNotification functionality with proper error handling.
     */
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

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->markAsRead();
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
    public function getUserNotifications(User $user, NotificationFilterData $filter): NotificationCollectionData
    {
        $query = $this->applyFilters(Notification::forUser($user->id), $filter);

        $paginator = $query->latest()->paginate($filter->perPage);

        return NotificationCollectionData::fromPaginator($paginator);
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function searchNotifications(User $user, NotificationSearchData $filter): NotificationCollectionData
    {
        $query = $this->applyFilters(Notification::forUser($user->id), $filter);
        $searchTerm = '%'.$filter->query.'%';
        $query->where(function (Builder $builder) use ($searchTerm): void {
            $builder
                ->where('data->title', 'like', $searchTerm)
                ->orWhere('data->message', 'like', $searchTerm);
        });

        $paginator = $query->latest()->paginate($filter->perPage);

        return NotificationCollectionData::fromPaginator($paginator);
    }

    /**
     * Handle stats functionality with proper error handling.
     */
    public function getUserNotificationStats(User $user): NotificationStatsData
    {
        $baseQuery = Notification::forUser($user->id);

        $total = (clone $baseQuery)->count();
        $read = (clone $baseQuery)->read()->count();
        $unread = (clone $baseQuery)->unread()->count();
        $urgent = (clone $baseQuery)->urgent()->count();
        $today = (clone $baseQuery)->whereDate('created_at', today())->count();
        $thisWeek = (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = (clone $baseQuery)->whereMonth('created_at', now()->month)->count();

        return new NotificationStatsData($total, $read, $unread, $urgent, $today, $thisWeek, $thisMonth);
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
