<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Product;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Low Stock Alert: :product', ['product' => $this->product->name]))
            ->line(__('Product :name is running low on stock.', ['name' => $this->product->name]))
            ->line(__('Current stock: :stock units', ['stock' => $this->product->stock_quantity]))
            ->line(__('Threshold: :threshold units', ['threshold' => $this->product->low_stock_threshold]))
            ->action(__('Manage Product'), route('filament.admin.resources.products.edit', $this->product))
            ->line(__('Please restock this product to avoid stockouts.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'current_stock' => $this->product->stock_quantity,
            'threshold' => $this->product->low_stock_threshold,
            'message' => __('Low stock alert for :product', ['product' => $this->product->name]),
        ];
    }

    public function toFilament(): FilamentNotification
    {
        return FilamentNotification::make()
            ->title(__('Low Stock Alert'))
            ->body(__('Product :name is running low on stock (:stock units remaining)', [
                'name' => $this->product->name,
                'stock' => $this->product->stock_quantity,
            ]))
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label(__('View Product'))
                    ->url(route('filament.admin.resources.products.edit', $this->product))
                    ->markAsRead(),
            ]);
    }
}
