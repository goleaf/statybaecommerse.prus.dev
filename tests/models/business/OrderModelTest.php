<?php declare(strict_types=1);

use App\Models\Order;

it('order model extends base Shop order', function (): void {
    $m = new Order();
    expect($m)->toBeInstanceOf(Order::class);
});
