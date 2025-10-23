<?php

declare(strict_types=1);

use App\Services\Pricing\PriceCalculator;
use Faker\Factory as FakerFactory;
use Tests\TestCase;

uses(TestCase::class);

it('keeps totals consistent across application contexts', function () {
    app()->setLocale('en');

    $calculator = app(PriceCalculator::class);
    $faker = FakerFactory::create();

    foreach (range(1, 100) as $iteration) {
        $items = collect(range(1, $faker->numberBetween(1, 5)))->map(fn () => [
            'price' => $faker->randomFloat(2, 0.01, 500),
            'quantity' => $faker->numberBetween(1, 5),
        ]);

        $discount = $faker->randomFloat(2, 0, 200);
        $shippingOverride = $faker->boolean() ? $faker->randomFloat(2, 0, 25) : null;
        $taxOverride = null;
        if ($faker->boolean()) {
            $taxOverride = $faker->boolean()
                ? $faker->randomFloat(3, 0, 0.25)
                : $faker->numberBetween(5, 25);
        }

        $breakdown = $calculator->calculate($items, $discount, $shippingOverride, $taxOverride);
        $raw = $breakdown->toArray();

        expect($raw['subtotal'])->toBeGreaterThanOrEqual(0.0)
            ->and($raw['discount'])->toBeGreaterThanOrEqual(0.0)
            ->and($raw['discount'])->toBeLessThanOrEqual($raw['subtotal'] + 0.0001)
            ->and($raw['tax'])->toBeGreaterThanOrEqual(0.0)
            ->and($raw['shipping'])->toBeGreaterThanOrEqual(0.0)
            ->and($raw['total'])->toBeGreaterThanOrEqual(0.0);

        $expectedTotal = $calculator->round($raw['subtotal'] - $raw['discount'] + $raw['tax'] + $raw['shipping']);
        expect($raw['total'])->toBe($expectedTotal);

        $formatted = $breakdown->formatted(locale: 'en');
        expect($formatted['subtotal'])->toBe($calculator->formatAmount($raw['subtotal'], $raw['currency'], 'en'))
            ->and($formatted['tax'])->toBe($calculator->formatAmount($raw['tax'], $raw['currency'], 'en'))
            ->and($formatted['total'])->toBe($calculator->formatAmount($raw['total'], $raw['currency'], 'en'));

        $reportTotal = $calculator->round($raw['total']);
        expect($reportTotal)->toBe($raw['total']);

        if ($shippingOverride !== null) {
            expect($raw['shipping'])->toBe($calculator->round(max(0.0, $shippingOverride)));
        }
    }
});
