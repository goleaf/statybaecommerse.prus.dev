<?php

declare(strict_types=1);

use App\Models\Order;
use App\Services\Payments\PaymentService;
use Tests\TestCase;

uses(TestCase::class);

it('process returns pending status and transaction payload', function () {
    $order = new Order;
    $order->grand_total_amount = 123.45;
    $order->currency_code = 'EUR';

    $svc = app(PaymentService::class);
    $res = $svc->process($order, ['provider' => 'manual']);

    expect($res)->toHaveKeys(['status', 'transaction'])
        ->and($res['status'])->toBe('pending')
        ->and($res['transaction'])->toHaveKeys(['id', 'provider', 'status', 'amount', 'currency']);
});
