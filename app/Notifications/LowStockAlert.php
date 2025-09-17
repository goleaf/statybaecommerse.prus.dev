<?php

declare (strict_types=1);
namespace App\Notifications;

use App\Models\Product;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
/**
 * LowStockAlert
 * 
 * Notification class for LowStockAlert user notifications with multi-channel delivery and customizable content.
 * 
 */
final class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * Initialize the class instance with required dependencies.
     * @param Product $product
     */
    public function __construct(public Product $product)
    {
    }
    /**
     * Handle via functionality with proper error handling.
     * @param object $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }
    /**
     * Handle toMail functionality with proper error handling.
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = method_exists($notifiable, 'preferredLocale') ? ($notifiable->preferredLocale() ?: app()->getLocale()) : app()->getLocale();

        return (new MailMessage())
            ->subject(__('notifications.stock.low_stock_subject', ['product' => $this->product->name], $locale))
            ->line(__('notifications.stock.low_stock_line', ['name' => $this->product->name], $locale))
            ->line(__('notifications.stock.current_stock', ['stock' => $this->product->stock_quantity], $locale))
            ->line(__('notifications.stock.threshold', ['threshold' => $this->product->low_stock_threshold], $locale))
            ->action(__('notifications.stock.manage_product', [], $locale), route('filament.admin.resources.products.edit', $this->product))
            ->line(__('notifications.stock.please_restock', [], $locale));
    }
    /**
     * Convert the instance to an array representation.
     * @param object $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'current_stock' => $this->product->stock_quantity,
            'threshold' => $this->product->low_stock_threshold,
            'message' => __('notifications.stock.low_stock_short', ['product' => $this->product->name]),
        ];
    }
    /**
     * Handle toFilament functionality with proper error handling.
     * @return FilamentNotification
     */
    public function toFilament(): FilamentNotification
    {
        return FilamentNotification::make()
            ->title(__('notifications.stock.low_stock_title'))
            ->body(__('notifications.stock.low_stock_body', ['name' => $this->product->name, 'stock' => $this->product->stock_quantity]))
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label(__('notifications.stock.view_product'))
                    ->url(route('filament.admin.resources.products.edit', $this->product))
                    ->markAsRead(),
            ]);
    }
}