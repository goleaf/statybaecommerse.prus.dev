<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;

interface ManageOrder
{
    public function handle(Order $order): mixed;
}
