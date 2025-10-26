<?php

declare(strict_types=1);

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters orders that were created on a specific day', function (): void {
    // Establish a deterministic window spanning three consecutive days.
    $targetDay = CarbonImmutable::parse('2025-01-15 12:00:00');
    $dayBefore = $targetDay->subDay();
    $dayAfter = $targetDay->addDay();

    // Create two orders anchored on the target day and two outside the window for contrast.
    Order::factory()->create(['number' => 'ORD-099', 'created_at' => $dayBefore]);
    Order::factory()->create(['number' => 'ORD-100', 'created_at' => $targetDay->setTime(8, 30)]);
    Order::factory()->create(['number' => 'ORD-101', 'created_at' => $targetDay->setTime(18, 45)]);
    Order::factory()->create(['number' => 'ORD-102', 'created_at' => $dayAfter]);

    // Use the dedicated scope to fetch orders strictly matching the chosen day.
    $matchingNumbers = Order::query()
        ->withoutGlobalScopes()
        ->createdOn($targetDay)
        ->orderBy('number')
        ->pluck('number')
        ->all();

    // Confirm only the orders from the target day are returned.
    expect($matchingNumbers)->toBe(['ORD-100', 'ORD-101']);
});

it('normalises unordered inputs for the createdBetween scope', function (): void {
    // Prepare two timestamps and deliberately present them to the scope in reverse order.
    $earliest = CarbonImmutable::parse('2025-02-01 00:00:00');
    $latest = CarbonImmutable::parse('2025-02-05 23:59:59');

    // Seed matching orders to verify both ends of the range are captured.
    Order::factory()->create(['number' => 'ORD-200', 'created_at' => $earliest->addHours(6)]);
    Order::factory()->create(['number' => 'ORD-201', 'created_at' => $latest->subHours(6)]);

    // Call the scope with reversed arguments so the helper must swap them internally.
    $results = Order::query()
        ->withoutGlobalScopes()
        ->createdBetween($latest, $earliest)
        ->orderBy('number')
        ->pluck('number')
        ->all();

    // Validate the query still returns both orders despite the inverted inputs.
    expect($results)->toBe(['ORD-200', 'ORD-201']);
});

it('orders records predictably through the orderedByName scope', function (): void {
    // Persist three orders with purposely shuffled identifiers to stress the ordering logic.
    Order::factory()->create(['number' => 'ORD-300']);
    Order::factory()->create(['number' => 'ORD-302']);
    Order::factory()->create(['number' => 'ORD-301']);

    // Exercise both ascending and descending behaviour for comprehensive coverage.
    $ascending = Order::query()->withoutGlobalScopes()->orderedByName()->pluck('number')->all();
    $descending = Order::query()->withoutGlobalScopes()->orderedByName('desc')->pluck('number')->all();

    // Ensure the results reflect the requested ordering direction.
    expect($ascending)->toBe(['ORD-300', 'ORD-301', 'ORD-302']);
    expect($descending)->toBe(['ORD-302', 'ORD-301', 'ORD-300']);
});

it('generates a unique order number when none is provided', function (): void {
    // Seed an order with a fixed number so collisions can be detected deterministically.
    $existingOrder = Order::factory()->create(['number' => 'ORD-AAAAAA']);

    // Create a barebones order without a number to trigger the model boot logic.
    $order = Order::query()->create([
        'status'          => 'pending',
        'subtotal'        => 0,
        'tax_amount'      => 0,
        'shipping_amount' => 0,
        'discount_amount' => 0,
        'total'           => 0,
        'currency'        => 'EUR',
    ]);

    // Assert the generated identifier follows the ORD-XXXXXX convention and remains unique.
    expect($order->number)
        ->toMatch('/^ORD-[A-Z0-9]{6}$/')
        ->not()->toBeNull()
        ->not()->toBe($existingOrder->number);
});
