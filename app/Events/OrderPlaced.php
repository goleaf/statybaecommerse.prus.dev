<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * OrderPlaced
 *
 * Domain event that signals a successfully persisted order so listeners can
 * trigger fulfilment, notifications, or analytics side effects.
 */
final class OrderPlaced
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance with the freshly saved order aggregate.
     */
    public function __construct(public readonly Order $order) {}
}
