<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @internal Domain object for normalized price totals.
 *
 * @implements Arrayable<string, float|string>
 */
final class PriceBreakdown implements Arrayable
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $discount,
        public readonly float $tax,
        public readonly float $shipping,
        public readonly float $total,
        public readonly string $currency,
        public readonly float $taxRate
    ) {}

    /**
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax'      => $this->tax,
            'shipping' => $this->shipping,
            'total'    => $this->total,
            'currency' => $this->currency,
            'tax_rate' => $this->taxRate,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function formatted(?string $locale = null): array
    {
        return [
            'subtotal' => format_money($this->subtotal, $this->currency, $locale),
            'discount' => format_money($this->discount, $this->currency, $locale),
            'tax'      => format_money($this->tax, $this->currency, $locale),
            'shipping' => format_money($this->shipping, $this->currency, $locale),
            'total'    => format_money($this->total, $this->currency, $locale),
        ];
    }
}
