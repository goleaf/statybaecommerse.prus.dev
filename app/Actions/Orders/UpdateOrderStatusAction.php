<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Models\Order;

/**
 * Single responsibility action for updating order status
 */
class UpdateOrderStatusAction
{
    public function execute(Order $order, string $newStatus, ?string $notes = null): Order
    {
        $oldStatus = $order->status;

        $order->update([
            'status'            => $newStatus,
            'status_updated_at' => now(),
            'status_notes'      => $notes,
        ]);

        // Log status change in order history
        $order->statusHistory()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes'      => $notes,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        return $order->fresh();
    }
}
