<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class OrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $action,
        public readonly array $orderData,
        public readonly ?string $message = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order',
            'action' => $this->action,
            'order_id' => $this->orderData['id'] ?? null,
            'order_number' => $this->orderData['order_number'] ?? null,
            'title' => $this->getTitle(),
            'message' => $this->message ?? $this->getMessage(),
            'data' => $this->orderData,
            'sent_at' => now()->toISOString(),
        ];
    }

    private function getTitle(): string
    {
        return match ($this->action) {
            'created' => __('notifications.order.created'),
            'updated' => __('notifications.order.updated'),
            'cancelled' => __('notifications.order.cancelled'),
            'completed' => __('notifications.order.completed'),
            'shipped' => __('notifications.order.shipped'),
            'delivered' => __('notifications.order.delivered'),
            'payment_received' => __('notifications.order.payment_received'),
            'payment_failed' => __('notifications.order.payment_failed'),
            'refund_processed' => __('notifications.order.refund_processed'),
            default => __('notifications.order.updated'),
        };
    }

    private function getMessage(): string
    {
        $orderNumber = $this->orderData['order_number'] ?? '#N/A';
        
        return match ($this->action) {
            'created' => __('notifications.order.created') . " #{$orderNumber}",
            'updated' => __('notifications.order.updated') . " #{$orderNumber}",
            'cancelled' => __('notifications.order.cancelled') . " #{$orderNumber}",
            'completed' => __('notifications.order.completed') . " #{$orderNumber}",
            'shipped' => __('notifications.order.shipped') . " #{$orderNumber}",
            'delivered' => __('notifications.order.delivered') . " #{$orderNumber}",
            'payment_received' => __('notifications.order.payment_received') . " #{$orderNumber}",
            'payment_failed' => __('notifications.order.payment_failed') . " #{$orderNumber}",
            'refund_processed' => __('notifications.order.refund_processed') . " #{$orderNumber}",
            default => __('notifications.order.updated') . " #{$orderNumber}",
        };
    }
}
