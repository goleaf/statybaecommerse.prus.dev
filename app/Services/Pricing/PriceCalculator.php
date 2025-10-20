<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Data\PriceBreakdown;
use App\Services\Taxes\TaxCalculator;
use ArrayAccess;
use Illuminate\Support\Collection;
use Throwable;

use function app_setting;
use function collect;
use function config;
use function current_currency;
use function data_get;
use function format_money;

final class PriceCalculator
{
    public function __construct(private readonly TaxCalculator $taxCalculator) {}

    /**
     * @param  iterable<int, array<string, mixed>|ArrayAccess<string, mixed>|object>  $items
     */
    public function calculate(
        iterable $items,
        float $discount = 0.0,
        ?float $shippingOverride = null,
        ?float $taxRateOverride = null,
        ?string $currency = null,
        ?string $taxZone = null
    ): PriceBreakdown {
        $currency ??= current_currency();
        /** @var Collection<int, array<string, mixed>|ArrayAccess<string, mixed>|object> $collection */
        $collection = $items instanceof Collection ? $items : collect($items);

        $subtotal = $this->round(
            $collection->reduce(
                function (float $carry, mixed $item): float {
                    $price = $this->toFloat(data_get($item, 'price', data_get($item, 'unit_price', 0.0)));
                    $quantity = $this->normalizeQuantity(data_get($item, 'quantity', 0));

                    return $carry + ($price * $quantity);
                },
                0.0
            )
        );

        $normalizedDiscount = $this->round($this->normalizeDiscount($discount, $subtotal));
        $shipping = $shippingOverride !== null
            ? $this->round(max(0.0, $shippingOverride))
            : $this->resolveShipping($subtotal);

        $taxable = max(0.0, $subtotal - $normalizedDiscount);
        $tax = $this->round(
            $this->taxCalculator->compute(
                $taxable,
                $taxZone,
                $taxRateOverride
            )
        );

        $total = $this->round(max(0.0, $subtotal - $normalizedDiscount + $shipping + $tax));
        $taxRate = $taxRateOverride !== null
            ? $this->normalizeRate($taxRateOverride)
            : $this->taxCalculator->getTaxRate($taxZone, false);

        return new PriceBreakdown(
            subtotal: $subtotal,
            discount: $normalizedDiscount,
            tax: $tax,
            shipping: $shipping,
            total: $total,
            currency: $currency,
            taxRate: $taxRate
        );
    }

    public function round(float $amount, ?int $precision = null): float
    {
        $precision ??= $this->precision();

        return round($amount, $precision);
    }

    public function formatAmount(float $amount, ?string $currency = null, ?string $locale = null): string
    {
        return format_money($this->round($amount), $currency ?? current_currency(), $locale);
    }

    private function precision(): int
    {
        return (int) config('pricing.rounding_precision', 2);
    }

    private function normalizeDiscount(float $discount, float $subtotal): float
    {
        if ($discount <= 0.0 || $subtotal <= 0.0) {
            return 0.0;
        }

        if ($discount > $subtotal) {
            return $subtotal;
        }

        return $discount;
    }

    private function resolveShipping(float $subtotal): float
    {
        $threshold = $this->setting('free_shipping_threshold', (float) config('pricing.shipping.free_threshold', 50.0));
        $rate = $this->setting('shipping_cost', (float) config('pricing.shipping.flat_rate', 5.99));

        if ($subtotal >= $threshold) {
            return 0.0;
        }

        return $this->round(max(0.0, $rate));
    }

    private function setting(string $key, float $default): float
    {
        try {
            return (float) app_setting($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    private function normalizeRate(float $rate): float
    {
        return $rate > 1 ? $rate / 100 : $rate;
    }

    private function toFloat(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    private function normalizeQuantity(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) round((float) $value);
        }

        return 0;
    }
}
