<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @phpstan-type Locale string
 */
final class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function build(): self
    {
        $locale = $this->resolveLocale();

        return $this
            ->locale($locale)
            ->subject(__('mail.order_confirmation_subject', ['number' => $this->order->number], $locale))
            ->markdown('emails.orders.placed', [
                'order' => $this->order,
                'orderUrl' => route('account.orders.detail', [
                    'locale' => $locale,
                    'number' => $this->order->number,
                ]),
            ]);
    }

    private function resolveLocale(): string
    {
        if (property_exists($this->order, 'locale') && filled($this->order->locale)) {
            return (string) $this->order->locale;
        }

        return app()->getLocale();
    }
}
