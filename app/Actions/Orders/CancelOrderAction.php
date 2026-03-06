<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Models\Order;

/**
 * Single responsibility action for cancelling orders
 */
class CancelOrderAction
{
    public function execute(Order $order, string $reason): Order
    {
        $order->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $reason,
            'cancelled_by'        => auth()->id(),
        ]);

        // Log cancellation in order history
        $order->statusHistory()->create([
            'old_status' => $order->getOriginal('status'),
            'new_status' => 'cancelled',
            'notes'      => "Order cancelled: {$reason}",
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        return $order->fresh();
    }
}
