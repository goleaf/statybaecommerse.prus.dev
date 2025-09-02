<?php declare(strict_types=1);

use App\Models\Order;

it('instantiates Order model', function (): void {
    expect(new Order())->toBeInstanceOf(Order::class);
});
