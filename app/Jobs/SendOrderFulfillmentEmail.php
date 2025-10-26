<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendOrderFulfillmentEmail queues a placeholder job so webhook processing can
 * trigger email delivery outside of the HTTP request lifecycle.
 */
final class SendOrderFulfillmentEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $orderId)
    {
    }

    /**
     * Handle the queued job. The implementation intentionally keeps logging
     * minimal to avoid leaking sensitive customer information during tests.
     */
    public function handle(): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order instanceof Order) {
            // The order might have been deleted after fulfillment so we exit
            // silently while emitting a debug log for observability.
            Log::warning('Fulfillment email skipped because order was missing.', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        Log::info('Queued fulfillment email for order.', [
            'order_id' => $order->id,
            'order_number' => $order->number,
        ]);
    }
}
