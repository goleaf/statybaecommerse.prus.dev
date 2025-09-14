<?php

declare (strict_types=1);
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
use Spatie\LaravelData\ValidationContext;
/**
 * ProductRequestData
 * 
 * Data transfer object for ProductRequestData structured data handling with validation and type safety.
 * 
 */
final class ProductRequestData extends Data
{
    /**
     * Initialize the class instance with required dependencies.
     * @param int $product_id
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string|null $message
     * @param int $requested_quantity
     */
    public function __construct(
        #[Required, IntegerType, Exists('products', 'id')]
        public int $product_id,
        #[Required, StringType, Max(255)]
        public string $name,
        #[Required, Email, Max(255)]
        public string $email,
        #[Nullable, StringType, Max(20)]
        public ?string $phone,
        #[Nullable, StringType, Max(1000)]
        public ?string $message,
        #[Required, IntegerType, Min(1), Max(999)]
        public int $requested_quantity
    )
    {
    }
    /**
     * Handle messages functionality with proper error handling.
     * @param ValidationContext $context
     * @return array
     */
    public static function messages(ValidationContext $context): array
    {
        return ['product_id.required' => __('translations.product_id_required'), 'product_id.exists' => __('translations.product_not_found'), 'name.required' => __('translations.name_required'), 'name.max' => __('translations.name_max_length'), 'email.required' => __('translations.email_required'), 'email.email' => __('translations.email_invalid'), 'email.max' => __('translations.email_max_length'), 'phone.max' => __('translations.phone_max_length'), 'message.max' => __('translations.message_max_length'), 'requested_quantity.required' => __('translations.quantity_required'), 'requested_quantity.integer' => __('translations.quantity_must_be_integer'), 'requested_quantity.min' => __('translations.quantity_min_value'), 'requested_quantity.max' => __('translations.quantity_max_value')];
    }
    /**
     * Handle attributes functionality with proper error handling.
     * @param ValidationContext $context
     * @return array
     */
    public static function attributes(ValidationContext $context): array
    {
        return ['product_id' => __('translations.product'), 'name' => __('translations.name'), 'email' => __('translations.email'), 'phone' => __('translations.phone'), 'message' => __('translations.message'), 'requested_quantity' => __('translations.quantity')];
    }
}