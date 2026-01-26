<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Product;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * LowStockAlert
 *
 * Notification class for LowStockAlert user notifications with multi-channel delivery and customizable content.
 */
final class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public Product $product)
    {
        // Share the product so every channel can present the same stock information.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Alert through both email and in-app notifications to prompt a quick restock.
        return ['mail', 'database'];
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Compose a concise summary showing current stock levels and thresholds.
        return (new MailMessage)
            ->subject(__('messages.low_stock_alert_product', ['product' => $this->product->name]))
            ->line(__('messages.product_name_is_running_low_on_stock', ['name' => $this->product->name]))
            ->line(__('messages.current_stock_stock_units', ['stock' => $this->product->stock_quantity]))
            ->line(__('messages.threshold_threshold_units', ['threshold' => $this->product->low_stock_threshold]))
            ->action(__('Manage Product'), route('filament.admin.resources.products.edit', $this->product))
            ->line(__('messages.please_restock_this_product_to_avoid_stockouts'));
    }

    /**
     * Convert the instance to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Persist the stock details so dashboards can render a useful summary.
        return [
            'product_id'    => $this->product->id,
            'product_name'  => $this->product->name,
            'product_sku'   => $this->product->sku,
            'current_stock' => $this->product->stock_quantity,
            'threshold'     => $this->product->low_stock_threshold,
            'message'       => __('messages.low_stock_alert_for_product', ['product' => $this->product->name]),
        ];
    }

    /**
     * Handle toFilament functionality with proper error handling.
     */
    public function toFilament(): FilamentNotification
    {
        // Provide a quick action so administrators can jump straight to the edit screen.
        return FilamentNotification::make()
            ->title(__('messages.low_stock_alert'))
            ->body(__('messages.product_name_is_running_low_on_stock_stock_units_remaining', [
                'name'  => $this->product->name,
                'stock' => $this->product->stock_quantity,
            ]))
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label(__('messages.view_product'))
                    ->url(route('filament.admin.resources.products.edit', $this->product))
                    ->markAsRead(),
            ]);
    }
}
