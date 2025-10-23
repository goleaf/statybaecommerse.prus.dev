<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('unit: customer resolver returns user name or null', function (): void {
    /** @var callable $resolver */
    $resolver = config('exports.entities.orders.columns.customer.resolver');

    // With user attached
    $user = User::factory()->make(['name' => 'Jane Doe']);
    $orderWithUser = Order::factory()->make();
    $orderWithUser->setRelation('user', $user);
    expect($resolver($orderWithUser))->toBe('Jane Doe');

    // Without user attached
    $orderWithoutUser = Order::factory()->make();
    $orderWithoutUser->unsetRelation('user');
    expect($resolver($orderWithoutUser))->toBeNull();
});

