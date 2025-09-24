<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

/**
 * ReviewData
 *
 * Data transfer object for ReviewData structured data handling with validation and type safety.
 */
final class ReviewData extends Data
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(
        #[Required, IntegerType, Exists('products', 'id')]
        public int $product_id,
        #[Required, IntegerType, Min(1), Max(5)]
        public int $rating,
        #[Nullable, StringType, Max(255)]
        public ?string $title,
        #[Nullable, StringType, Max(2000)]
        public ?string $content,
        #[Required, StringType, Max(255)]
        public string $reviewer_name,
        #[Required, Email, Max(255)]
        public string $reviewer_email
    ) {}
}
