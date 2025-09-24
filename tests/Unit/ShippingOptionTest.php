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
        'price' => 25.5,
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
        'price' => 20.0,
        'min_weight' => 1,
        'max_weight' => 10,
        'min_order_amount' => 50,
        'max_order_amount' => 500,
    ]);

    // Eligible order
    expect($shippingOption->calculatePriceForOrder(5, 100))->toBe(20.0);

    // Ineligible due to weight
    expect($shippingOption->calculatePriceForOrder(15, 100))->toBe(0.0);

    // Ineligible due to order amount
    expect($shippingOption->calculatePriceForOrder(5, 25))->toBe(0.0);
});

test('shipping option scopes work correctly', function () {
    $zone1 = Zone::factory()->create();
    $zone2 = Zone::factory()->create();
    $zone3 = Zone::factory()->create();
    $zone4 = Zone::factory()->create();

    ShippingOption::create([
        'name' => 'DHL Express',
        'slug' => 'dhl-express-1',
        'carrier_name' => 'DHL',
        'service_type' => 'Express',
        'price' => 15.99,
        'currency_code' => 'EUR',
        'zone_id' => $zone1->id,
        'is_enabled' => true,
        'is_default' => false,
    ]);

    ShippingOption::create([
        'name' => 'UPS Standard',
        'slug' => 'ups-standard-1',
        'carrier_name' => 'UPS',
        'service_type' => 'Standard',
        'price' => 12.99,
        'currency_code' => 'EUR',
        'zone_id' => $zone2->id,
        'is_enabled' => false,
        'is_default' => false,
    ]);

    ShippingOption::create([
        'name' => 'FedEx Priority',
        'slug' => 'fedex-priority-1',
        'carrier_name' => 'FedEx',
        'service_type' => 'Priority',
        'price' => 20.99,
        'currency_code' => 'EUR',
        'zone_id' => $zone3->id,
        'is_enabled' => true,
        'is_default' => true,
    ]);

    ShippingOption::create([
        'name' => 'DHL Economy',
        'slug' => 'dhl-economy-1',
        'carrier_name' => 'DHL',
        'service_type' => 'Economy',
        'price' => 8.99,
        'currency_code' => 'EUR',
        'zone_id' => $zone4->id,
        'is_enabled' => true,
        'is_default' => false,
    ]);

    expect(ShippingOption::count())->toBe(4);
    expect(ShippingOption::enabled()->count())->toBe(3);  // 3 enabled
    expect(ShippingOption::default()->count())->toBe(1);
    expect(ShippingOption::byCarrier('DHL')->count())->toBe(2);  // 2 DHL options
    expect(ShippingOption::byZone($zone1->id)->count())->toBe(1);
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
