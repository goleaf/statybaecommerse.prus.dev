<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

use App\Data\Pricing\VariantPriceResult;
use App\Models\ProductVariant;
use Carbon\CarbonInterface;

interface PriceCalculationStrategy
{
    public function calculate(
        ProductVariant $variant,
        array $context,
        CarbonInterface $moment
    ): VariantPriceResult;
}
