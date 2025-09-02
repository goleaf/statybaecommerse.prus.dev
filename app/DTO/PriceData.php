<?php declare(strict_types=1);

namespace App\DTO;

// Legacy Shopper Price helper removed - using native Laravel decimal handling

class PriceData
{
    public function __construct(
        public float $value,
        public ?float $compare,
        public ?float $percentage,
    ) {}
}
