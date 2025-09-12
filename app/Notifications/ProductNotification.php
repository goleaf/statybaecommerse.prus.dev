<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class ProductNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $action,
        public readonly array $productData,
        public readonly ?string $message = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'product',
            'action' => $this->action,
            'product_id' => $this->productData['id'] ?? null,
            'product_name' => $this->productData['name'] ?? null,
            'title' => $this->getTitle(),
            'message' => $this->message ?? $this->getMessage(),
            'data' => $this->productData,
            'sent_at' => now()->toISOString(),
        ];
    }

    private function getTitle(): string
    {
        return match ($this->action) {
            'created' => __('notifications.product.created'),
            'updated' => __('notifications.product.updated'),
            'deleted' => __('notifications.product.deleted'),
            'low_stock' => __('notifications.product.low_stock'),
            'out_of_stock' => __('notifications.product.out_of_stock'),
            'back_in_stock' => __('notifications.product.back_in_stock'),
            'price_changed' => __('notifications.product.price_changed'),
            'review_added' => __('notifications.product.review_added'),
            default => __('notifications.product.updated'),
        };
    }

    private function getMessage(): string
    {
        $productName = $this->productData['name'] ?? 'Unknown Product';
        
        return match ($this->action) {
            'created' => __('notifications.product.created') . ": {$productName}",
            'updated' => __('notifications.product.updated') . ": {$productName}",
            'deleted' => __('notifications.product.deleted') . ": {$productName}",
            'low_stock' => __('notifications.product.low_stock') . ": {$productName}",
            'out_of_stock' => __('notifications.product.out_of_stock') . ": {$productName}",
            'back_in_stock' => __('notifications.product.back_in_stock') . ": {$productName}",
            'price_changed' => __('notifications.product.price_changed') . ": {$productName}",
            'review_added' => __('notifications.product.review_added') . ": {$productName}",
            default => __('notifications.product.updated') . ": {$productName}",
        };
    }
}
