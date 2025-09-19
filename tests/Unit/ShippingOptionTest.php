<?php

declare(strict_types=1);

use App\Models\ShippingOption;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->zone = Zone::factory()->create();
});

test('can create shipping option', function () {
    $shippingOption = ShippingOption::factory()->create([
        'zone_id' => $this->zone->id,
        'name' => 'DHL Express',
        'carrier_name' => 'DHL',
        'service_type' => 'Express',
        'price' => 15.99,
    ]);

    expect($shippingOption->name)->toBe('DHL Express');
    expect($shippingOption->carrier_name)->toBe('DHL');
    expect($shippingOption->service_type)->toBe('Express');
    expect($shippingOption->price)->toBe('15.99');
    expect($shippingOption->zone_id)->toBe($this->zone->id);
});

test('shipping option belongs to zone', function () {
    $shippingOption = ShippingOption::factory()->create([
        'zone_id' => $this->zone->id,
    ]);

    expect($shippingOption->zone)->toBeInstanceOf(Zone::class);
    expect($shippingOption->zone->id)->toBe($this->zone->id);
});

test('shipping option has formatted price accessor', function () {
    $shippingOption = ShippingOption::factory()->create([
        'price' => 25.50,
        'currency_code' => 'EUR',
    ]);

    expect($shippingOption->formatted_price)->toBe('25.50 EUR');
});

test('shipping option has estimated delivery text accessor', function () {
    $shippingOption = ShippingOption::factory()->create([
        'estimated_days_min' => 2,
        'estimated_days_max' => 4,
    ]);

    expect($shippingOption->estimated_delivery_text)->toBe('2-4 days');

    $shippingOption->update([
        'estimated_days_min' => 3,
        'estimated_days_max' => 3,
    ]);

    expect($shippingOption->estimated_delivery_text)->toBe('3 days');

    $shippingOption->update([
        'estimated_days_min' => null,
        'estimated_days_max' => null,
    ]);

    expect($shippingOption->estimated_delivery_text)->toBe('Standard delivery');
});

test('shipping option can check weight eligibility', function () {
    $shippingOption = ShippingOption::factory()->create([
        'min_weight' => 1,
        'max_weight' => 10,
    ]);

    expect($shippingOption->isEligibleForWeight(5))->toBeTrue();
    expect($shippingOption->isEligibleForWeight(0.5))->toBeFalse();
    expect($shippingOption->isEligibleForWeight(15))->toBeFalse();

    // Test without weight limits
    $shippingOption->update(['min_weight' => null, 'max_weight' => null]);
    expect($shippingOption->isEligibleForWeight(100))->toBeTrue();
});

test('shipping option can check order amount eligibility', function () {
    $shippingOption = ShippingOption::factory()->create([
        'min_order_amount' => 50,
        'max_order_amount' => 500,
    ]);

    expect($shippingOption->isEligibleForOrderAmount(100))->toBeTrue();
    expect($shippingOption->isEligibleForOrderAmount(25))->toBeFalse();
    expect($shippingOption->isEligibleForOrderAmount(600))->toBeFalse();

    // Test without amount limits
    $shippingOption->update(['min_order_amount' => null, 'max_order_amount' => null]);
    expect($shippingOption->isEligibleForOrderAmount(1000))->toBeTrue();
});

test('shipping option can calculate price for order', function () {
    $shippingOption = ShippingOption::factory()->create([
        'price' => 20.00,
        'min_weight' => 1,
        'max_weight' => 10,
        'min_order_amount' => 50,
        'max_order_amount' => 500,
    ]);

    // Eligible order
    expect($shippingOption->calculatePriceForOrder(5, 100))->toBe(20.00);
    
    // Ineligible due to weight
    expect($shippingOption->calculatePriceForOrder(15, 100))->toBe(0.0);
    
    // Ineligible due to order amount
    expect($shippingOption->calculatePriceForOrder(5, 25))->toBe(0.0);
});

test('shipping option scopes work correctly', function () {
    ShippingOption::factory()->create(['is_enabled' => true]);
    ShippingOption::factory()->create(['is_enabled' => false]);
    ShippingOption::factory()->create(['is_default' => true]);
    ShippingOption::factory()->create(['carrier_name' => 'DHL']);

    expect(ShippingOption::enabled()->count())->toBe(1);
    expect(ShippingOption::default()->count())->toBe(1);
    expect(ShippingOption::byCarrier('DHL')->count())->toBe(1);
    expect(ShippingOption::byZone($this->zone->id)->count())->toBe(4);
});

test('shipping option casts work correctly', function () {
    $shippingOption = ShippingOption::factory()->create([
        'price' => '25.99',
        'is_enabled' => '1',
        'is_default' => '0',
        'min_weight' => '5',
        'max_weight' => '20',
        'estimated_days_min' => '2',
        'estimated_days_max' => '5',
    ]);

    expect($shippingOption->price)->toBe('25.99');
    expect($shippingOption->is_enabled)->toBeTrue();
    expect($shippingOption->is_default)->toBeFalse();
    expect($shippingOption->min_weight)->toBe(5);
    expect($shippingOption->max_weight)->toBe(20);
    expect($shippingOption->estimated_days_min)->toBe(2);
    expect($shippingOption->estimated_days_max)->toBe(5);
});
