<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;

/**
 * ManageOrder
 *
 * Interface contract defining required methods and behavior.
 */
interface ManageOrder
{
    public function handle(Order $order): mixed;
}
