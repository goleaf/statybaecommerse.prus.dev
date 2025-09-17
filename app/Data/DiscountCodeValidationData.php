<?php

declare (strict_types=1);
namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
/**
 * DiscountCodeValidationData
 * 
 * Data transfer object for DiscountCodeValidationData structured data handling with validation and type safety.
 * 
 */
final class DiscountCodeValidationData extends Data
{
    /**
     * Initialize the class instance with required dependencies.
     * @param string $code
     */
    public function __construct(
        #[Required, StringType, Max(50)]
        public string $code
    )
    {
    }
}