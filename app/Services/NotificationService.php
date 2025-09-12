<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\TestNotification;
use App\Notifications\OrderNotification;
use App\Notifications\ProductNotification;
use App\Notifications\UserNotification;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

final class NotificationService
{
    // Legacy methods for backward compatibility
    public function sendToAdmins(string $title, string $message, string $type = 'info'): void
    {
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['administrator', 'manager']);
        })->get();

        foreach ($adminUsers as $user) {
            $user->notify(new TestNotification($title, $message, $type));
        }
    }

    public function sendToUser(User $user, string $title, string $message, string $type = 'info'): void
    {
        $user->notify(new TestNotification($title, $message, $type));
    }

    public function sendToUsers(Collection $users, string $title, string $message, string $type = 'info'): void
    {
        foreach ($users as $user) {
            $user->notify(new TestNotification($title, $message, $type));
        }
    }

    // Order notifications
    public function notifyOrderCreated(Order $order): void
    {
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total,
            'status' => $order->status,
            'customer_name' => $order->user->name ?? 'Guest',
        ];

        // Notify customer
        if ($order->user) {
            $order->user->notify(new OrderNotification('created', $orderData));
        }

        // Notify admins
        $this->notifyAdmins(new OrderNotification('created', $orderData));
    }

    public function notifyOrderUpdated(Order $order): void
    {
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total,
            'status' => $order->status,
            'customer_name' => $order->user->name ?? 'Guest',
        ];

        if ($order->user) {
            $order->user->notify(new OrderNotification('updated', $orderData));
        }

        $this->notifyAdmins(new OrderNotification('updated', $orderData));
    }

    public function notifyOrderShipped(Order $order): void
    {
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'tracking_number' => $order->tracking_number,
            'shipping_method' => $order->shipping_method,
        ];

        if ($order->user) {
            $order->user->notify(new OrderNotification('shipped', $orderData));
        }
    }

    public function notifyOrderDelivered(Order $order): void
    {
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'delivered_at' => $order->delivered_at,
        ];

        if ($order->user) {
            $order->user->notify(new OrderNotification('delivered', $orderData));
        }
    }

    public function notifyPaymentReceived(Order $order): void
    {
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $order->total,
            'payment_method' => $order->payment_method,
        ];

        if ($order->user) {
            $order->user->notify(new OrderNotification('payment_received', $orderData));
        }

        $this->notifyAdmins(new OrderNotification('payment_received', $orderData));
    }

    // Product notifications
    public function notifyProductCreated(Product $product): void
    {
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
        ];

        $this->notifyAdmins(new ProductNotification('created', $productData));
    }

    public function notifyProductUpdated(Product $product): void
    {
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
        ];

        $this->notifyAdmins(new ProductNotification('updated', $productData));
    }

    public function notifyLowStock(Product $product): void
    {
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
            'min_stock_level' => $product->min_stock_level,
        ];

        $this->notifyAdmins(new ProductNotification('low_stock', $productData));
    }

    public function notifyOutOfStock(Product $product): void
    {
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
        ];

        $this->notifyAdmins(new ProductNotification('out_of_stock', $productData));
    }

    public function notifyBackInStock(Product $product): void
    {
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'stock_quantity' => $product->stock_quantity,
        ];

        // Notify users who were waiting for this product
        $waitingUsers = User::whereHas('wishlist', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->get();

        foreach ($waitingUsers as $user) {
            $user->notify(new ProductNotification('back_in_stock', $productData));
        }

        $this->notifyAdmins(new ProductNotification('back_in_stock', $productData));
    }

    // User notifications
    public function notifyUserRegistered(User $user): void
    {
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'registered_at' => $user->created_at,
        ];

        $this->notifyAdmins(new UserNotification('registered', $userData));
    }

    public function notifyProfileUpdated(User $user): void
    {
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at,
        ];

        $user->notify(new UserNotification('profile_updated', $userData));
    }

    // System notifications
    public function notifyMaintenanceStarted(): void
    {
        $this->notifyAllUsers(new SystemNotification('maintenance_started'));
    }

    public function notifyMaintenanceCompleted(): void
    {
        $this->notifyAllUsers(new SystemNotification('maintenance_completed'));
    }

    public function notifySecurityAlert(string $message): void
    {
        $this->notifyAdmins(new SystemNotification('security_alert', [], $message));
    }

    // Helper methods
    private function notifyAdmins($notification): void
    {
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['administrator', 'manager']);
        })->get();

        foreach ($adminUsers as $user) {
            $user->notify($notification);
        }
    }

    private function notifyAllUsers($notification): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            $user->notify($notification);
        }
    }

    // Utility methods
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function getRecentNotifications(User $user, int $limit = 10): Collection
    {
        return $user->notifications()->latest()->limit($limit)->get();
    }

    public function deleteNotification(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
            return true;
        }
        
        return false;
    }

    public function deleteAllNotifications(User $user): int
    {
        return $user->notifications()->delete();
    }
}
