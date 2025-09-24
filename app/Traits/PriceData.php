<?php

declare(strict_types=1);

namespace App\Traits;

// Legacy Shopper Price helper removed - using native Laravel decimal handling
/**
 * PriceData
 *
 * Trait providing PriceData functionality that can be reused across multiple classes with consistent behavior.
 */
class PriceData
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public float $value, public ?float $compare, public ?float $percentage) {}
}
