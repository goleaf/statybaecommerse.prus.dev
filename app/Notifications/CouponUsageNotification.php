<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CouponUsage;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * CouponUsageNotification
 *
 * Notification dispatched when a coupon usage is registered so the customer receives timely updates.
 */
final class CouponUsageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Store the usage that triggered the notification for later channel formatting.
     */
    public function __construct(public readonly CouponUsage $couponUsage) {}

    /**
     * Deliver through both mail and database channels to cover inbox and in-app inbox experiences.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Use multiple channels so the user receives both an email and a dashboard alert.
        return ['mail', 'database'];
    }

    /**
     * Build a concise transactional email summarising the discount that was applied.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Include the formatted discount and timestamp for transparency.
        return (new MailMessage)
            ->subject(__('notifications.coupon_usage.subject'))
            ->line(__('notifications.coupon_usage.intro', ['amount' => $this->couponUsage->formatted_discount]))
            ->line(__('notifications.coupon_usage.used_at', ['date' => $this->couponUsage->formatted_used_at]));
    }

    /**
     * Persist a lightweight payload for the notification center.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Provide structured data so the UI can render a detailed notification card.
        return [
            'coupon_id'          => $this->couponUsage->coupon_id,
            'order_id'           => $this->couponUsage->order_id,
            'discount_amount'    => $this->couponUsage->discount_amount,
            'formatted_discount' => $this->couponUsage->formatted_discount,
            'used_at'            => $this->couponUsage->used_at instanceof CarbonInterface
                ? $this->couponUsage->used_at->toIso8601String()
                : null,
        ];
    }
}
