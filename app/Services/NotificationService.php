<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class NotificationService
{
    /**
     * Create a new notification for a user
     */
    public function createNotification(
        Model $notifiable,
        string $type,
        array $data = [],
        bool $urgent = false,
        ?string $color = null,
        array $tags = []
    ): Notification {
        $notificationData = array_merge([
            'type' => $type,
            'urgent' => $urgent,
            'color' => $color,
            'tags' => $tags,
        ], $data);

        return Notification::create([
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'data' => $notificationData,
        ]);
    }

    /**
     * Create order-related notifications
     */
    public function createOrderNotification(
        User $user,
        string $action,
        array $orderData = [],
        bool $urgent = false
    ): Notification {
        $data = [
            'title' => __('notifications.order.'.$action),
            'message' => $this->getOrderMessage($action, $orderData),
            'type' => 'order',
            'order_id' => $orderData['id'] ?? null,
            'order_number' => $orderData['number'] ?? null,
        ];

        return $this->createNotification($user, 'App\Notifications\OrderNotification', $data, $urgent);
    }

    /**
     * Create product-related notifications
     */
    public function createProductNotification(
        User $user,
        string $action,
        array $productData = [],
        bool $urgent = false
    ): Notification {
        $data = [
            'title' => __('notifications.product.'.$action),
            'message' => $this->getProductMessage($action, $productData),
            'type' => 'product',
            'product_id' => $productData['id'] ?? null,
            'product_name' => $productData['name'] ?? null,
        ];

        return $this->createNotification($user, 'App\Notifications\ProductNotification', $data, $urgent);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsReadForUser(User $user): int
    {
        return Notification::markAllAsReadForUser($user->id);
    }

    /**
     * Get user's notifications with pagination
     */
    public function getUserNotifications(
        User $user,
        int $perPage = 25,
        ?string $type = null,
        ?bool $read = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $query = Notification::forUser($user->id);

        if ($type) {
            $query->byType($type);
        }

        if ($read !== null) {
            $query = $read ? $query->read() : $query->unread();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get order message based on action
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
     * Get product message based on action
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
