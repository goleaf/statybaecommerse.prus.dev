<?php

declare (strict_types=1);
namespace App\Actions\Payment;

use App\Contracts\ManageOrder;
use App\Models\Order;
/**
 * PayWithCash
 * 
 * Action class for PayWithCash single-purpose operations with validation, error handling, and result reporting.
 * 
 */
class PayWithCash implements ManageOrder
{
    /**
     * Handle the job, event, or request processing.
     * @param Order $order
     * @return mixed
     */
    public function handle(Order $order): mixed
    {
        session()->forget('checkout');
        return redirect()->route('checkout.confirmation', ['number' => $order->number]);
    }
}