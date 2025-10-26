<?php

declare(strict_types=1);

use App\Data\Pricing\PriceBreakdown;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Support\Facades\Config;

describe('PriceCalculator', function () {
    beforeEach(function (): void {
        Config::set('pricing', [
            'currency' => 'EUR',
            'rounding' => [
                'precision' => 2,
                'mode'      => PHP_ROUND_HALF_UP,
            ],
            'vat' => [
                'rate'        => 21.0,
                'setting_key' => 'tax_rate',
            ],
            'shipping' => [
                'flat_rate'                  => 5.99,
                'flat_rate_setting_key'      => 'shipping_cost',
                'free_threshold'             => 100.0,
                'free_threshold_setting_key' => 'free_shipping_threshold',
            ],
        ]);
    });

    test('computes breakdown with configured VAT and shipping thresholds', function (): void {
        $calculator = app(PriceCalculator::class);

        $breakdown = $calculator->breakdown(50.0);

        expect($breakdown)
            ->toBeInstanceOf(PriceBreakdown::class)
            ->and($breakdown->subtotal)->toBe(50.0)
            ->and($breakdown->discount)->toBe(0.0)
            ->and($breakdown->shipping)->toBe(5.99)
            ->and($breakdown->tax)->toBe(10.5)
            ->and($breakdown->total)->toBe(66.49)
            ->and($breakdown->currency)->toBe('EUR');
    });

    test('produces consistent totals across randomised scenarios', function (): void {
        $calculator = app(PriceCalculator::class);

        foreach (range(1, 100) as $i) {
            $subtotal = mt_rand(0, 200_000) / 100;
            $discount = mt_rand(0, (int) round($subtotal * 120));
            $discount /= 100;
            $shippingOverride = random_int(0, 1) === 1 ? mt_rand(0, 10_000) / 100 : null;
            $vatRate = [null, 21.0, 9.0, 5.5][array_rand([0, 1, 2, 3])];

            $breakdown = $calculator->breakdown($subtotal, $discount, $shippingOverride, $vatRate);

            expect($breakdown->subtotal)->toBeGreaterThanOrEqual(0.0)
                ->and($breakdown->discount)->toBeGreaterThanOrEqual(0.0)
                ->and($breakdown->discount)->toBeLessThanOrEqual($breakdown->subtotal + 0.0001)
                ->and($breakdown->tax)->toBeGreaterThanOrEqual(0.0)
                ->and($breakdown->shipping)->toBeGreaterThanOrEqual(0.0);

            $recomputedTotal = round($breakdown->taxableAmount + $breakdown->tax + $breakdown->shipping, 2);
            expect($breakdown->total)->toBe($recomputedTotal);
        }
    });
});
