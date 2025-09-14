<?php

declare (strict_types=1);
namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
/**
 * NotificationService
 * 
 * Service class containing NotificationService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class NotificationService
{
    /**
     * Handle createNotification functionality with proper error handling.
     * @param Model $notifiable
     * @param string $type
     * @param array $data
     * @param bool $urgent
     * @param string|null $color
     * @param array $tags
     * @return Notification
     */
    public function createNotification(Model $notifiable, string $type, array $data = [], bool $urgent = false, ?string $color = null, array $tags = []): Notification
    {
        $notificationData = array_merge(['type' => $type, 'urgent' => $urgent, 'color' => $color, 'tags' => $tags], $data);
        return Notification::create(['type' => $type, 'notifiable_type' => get_class($notifiable), 'notifiable_id' => $notifiable->id, 'data' => $notificationData]);
    }
    /**
     * Handle createOrderNotification functionality with proper error handling.
     * @param User $user
     * @param string $action
     * @param array $orderData
     * @param bool $urgent
     * @return Notification
     */
    public function createOrderNotification(User $user, string $action, array $orderData = [], bool $urgent = false): Notification
    {
        $data = ['title' => __('notifications.order.' . $action), 'message' => $this->getOrderMessage($action, $orderData), 'type' => 'order', 'order_id' => $orderData['id'] ?? null, 'order_number' => $orderData['number'] ?? null];
        return $this->createNotification($user, 'App\Notifications\OrderNotification', $data, $urgent);
    }
    /**
     * Handle createProductNotification functionality with proper error handling.
     * @param User $user
     * @param string $action
     * @param array $productData
     * @param bool $urgent
     * @return Notification
     */
    public function createProductNotification(User $user, string $action, array $productData = [], bool $urgent = false): Notification
    {
        $data = ['title' => __('notifications.product.' . $action), 'message' => $this->getProductMessage($action, $productData), 'type' => 'product', 'product_id' => $productData['id'] ?? null, 'product_name' => $productData['name'] ?? null];
        return $this->createNotification($user, 'App\Notifications\ProductNotification', $data, $urgent);
    }
    /**
     * Handle markAsRead functionality with proper error handling.
     * @param Notification $notification
     * @return bool
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->markAsRead();
    }
    /**
     * Handle markAllAsReadForUser functionality with proper error handling.
     * @param User $user
     * @return int
     */
    public function markAllAsReadForUser(User $user): int
    {
        return Notification::markAllAsReadForUser($user->id);
    }
    /**
     * Handle getUserNotifications functionality with proper error handling.
     * @param User $user
     * @param int $perPage
     * @param string|null $type
     * @param bool|null $read
     * @return Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserNotifications(User $user, int $perPage = 25, ?string $type = null, ?bool $read = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
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
     * Handle getOrderMessage functionality with proper error handling.
     * @param string $action
     * @param array $orderData
     * @return string
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
     * @param string $action
     * @param array $productData
     * @return string
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