<?php

declare(strict_types=1);

namespace App\Data\Pricing;

use App\Models\Order;
use App\Services\Pricing\PriceConfiguration;

final class PriceBreakdown
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $discount,
        public readonly float $taxableAmount,
        public readonly float $tax,
        public readonly float $shipping,
        public readonly float $total,
        public readonly string $currency,
        public readonly float $vatRate,
    ) {}

    /**
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        return [
            'subtotal'        => $this->subtotal,
            'discount_amount' => $this->discount,
            'taxable_amount'  => $this->taxableAmount,
            'tax_amount'      => $this->tax,
            'shipping_amount' => $this->shipping,
            'total'           => $this->total,
            'currency'        => $this->currency,
            'vat_rate'        => $this->vatRate,
        ];
    }

    /**
     * @return array<string, float|string>
     */
    public function toSummary(): array
    {
        return $this->toArray() + [
            'formatted_subtotal'        => app_money_format($this->subtotal, $this->currency),
            'formatted_discount_amount' => app_money_format($this->discount, $this->currency),
            'formatted_taxable_amount'  => app_money_format($this->taxableAmount, $this->currency),
            'formatted_tax_amount'      => app_money_format($this->tax, $this->currency),
            'formatted_shipping_amount' => app_money_format($this->shipping, $this->currency),
            'formatted_total'           => app_money_format($this->total, $this->currency),
        ];
    }

    /**
     * Map the totals into the lean structure that our public contract expects.
     *
     * @return array<string, float|string>
     */
    public function toContractTotals(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'tax'      => $this->tax,
            'shipping' => $this->shipping,
            'discount' => $this->discount,
            'total'    => $this->total,
            'currency' => $this->currency,
        ];
    }

    public static function fromOrder(Order $order, PriceConfiguration $configuration): self
    {
        $subtotal = (float) $order->subtotal;
        $discount = (float) ($order->discount_amount ?? 0.0);
        $tax = (float) ($order->tax_amount ?? 0.0);
        $shipping = (float) ($order->shipping_amount ?? 0.0);
        $total = (float) $order->total;
        $currency = (string) ($order->currency ?? $configuration->currency());
        $taxable = max(0.0, $subtotal - $discount);
        $vatRate = $taxable > 0.0 ? $tax / $taxable : $configuration->vatRate();

        return new self(
            subtotal: $configuration->round($subtotal),
            discount: $configuration->round($discount),
            taxableAmount: $configuration->round($taxable),
            tax: $configuration->round($tax),
            shipping: $configuration->round($shipping),
            total: $configuration->round($total),
            currency: $currency,
            vatRate: max(0.0, $vatRate),
        );
    }
}
