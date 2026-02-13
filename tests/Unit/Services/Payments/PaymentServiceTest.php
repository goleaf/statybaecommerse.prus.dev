<?php

declare(strict_types=1);

use App\Models\Order;
use App\Services\Payments\PaymentService;

it('always emits eur currency for payment transactions', function (): void {
    $order = new Order;
    $order->setAttribute('grand_total_amount', 199.99);
    $order->setAttribute('currency_code', 'USD');

    $result = (new PaymentService)->process($order, [
        'provider' => 'card',
    ]);

    expect(data_get($result, 'transaction.currency'))->toBe('EUR');
    expect(data_get($result, 'transaction.amount'))->toBe(199.99);
});

