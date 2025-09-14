<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

final class DiscountCodeValidationData extends Data
{
    public function __construct(
        #[Required, StringType, Max(50)]
        public string $code,
    ) {
    }
}
