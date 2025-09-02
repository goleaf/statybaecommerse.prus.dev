<?php declare(strict_types=1);

namespace App\Actions\Payment;

use App\Contracts\ManageOrder;
use App\Models\Order;

class PayWithCash implements ManageOrder
{
    public function handle(Order $order): mixed
    {
        session()->forget('checkout');

        return redirect()->route('checkout.confirmation', ['number' => $order->number]);
    }
}
