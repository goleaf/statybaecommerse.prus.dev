<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build(): self
    {
        // Ensure the email is built using the order's locale when available
        $locale = method_exists($this->order, 'getAttribute') ? ($this->order->getAttribute('locale') ?? app()->getLocale()) : app()->getLocale();

        return $this
            ->locale($locale)
            ->subject(__('mail.order_confirmation_subject', ['number' => $this->order->number]))
            ->markdown('emails.orders.placed', [
                'order' => $this->order,
                'orderUrl' => route('account.orders.detail', ['locale' => $locale, 'number' => $this->order->number]),
            ]);
    }
}
