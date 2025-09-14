<?php

declare (strict_types=1);
namespace App\Data;

use App\Rules\UrlRule;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Ip;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\ValidationContext;
/**
 * CampaignClickData
 * 
 * Data transfer object for CampaignClickData structured data handling with validation and type safety.
 * 
 */
final class CampaignClickData extends Data
{
    /**
     * Initialize the class instance with required dependencies.
     * @param int $campaign_id
     * @param string|null $session_id
     * @param string|null $ip_address
     * @param string|null $user_agent
     * @param string $click_type
     * @param string|null $clicked_url
     * @param int|null $customer_id
     * @param string|null $clicked_at
     * @param string|null $referer
     * @param string|null $device_type
     * @param string|null $browser
     * @param string|null $os
     * @param string|null $country
     * @param string|null $city
     * @param string|null $utm_source
     * @param string|null $utm_medium
     * @param string|null $utm_campaign
     * @param string|null $utm_term
     * @param string|null $utm_content
     * @param float|null $conversion_value
     * @param bool|null $is_converted
     * @param array|null $conversion_data
     */
    public function __construct(
        #[Required, IntegerType, Exists('discount_campaigns', 'id')]
        public int $campaign_id,
        #[Nullable, StringType, Max(255)]
        public ?string $session_id,
        #[Nullable, Ip, Max(45)]
        public ?string $ip_address,
        #[Nullable, StringType, Max(500)]
        public ?string $user_agent,
        #[Required, StringType, In(['cta', 'banner', 'link', 'button', 'image'])]
        public string $click_type,
        #[Nullable, StringType, Max(500)]
        public ?string $clicked_url,
        #[Nullable, IntegerType, Exists('users', 'id')]
        public ?int $customer_id,
        #[Nullable, Date]
        public ?string $clicked_at,
        #[Nullable, StringType, Max(500)]
        public ?string $referer,
        #[Nullable, StringType, In(['desktop', 'mobile', 'tablet'])]
        public ?string $device_type,
        #[Nullable, StringType, Max(100)]
        public ?string $browser,
        #[Nullable, StringType, Max(100)]
        public ?string $os,
        #[Nullable, StringType, Max(100)]
        public ?string $country,
        #[Nullable, StringType, Max(100)]
        public ?string $city,
        #[Nullable, StringType, Max(100)]
        public ?string $utm_source,
        #[Nullable, StringType, Max(100)]
        public ?string $utm_medium,
        #[Nullable, StringType, Max(100)]
        public ?string $utm_campaign,
        #[Nullable, StringType, Max(100)]
        public ?string $utm_term,
        #[Nullable, StringType, Max(100)]
        public ?string $utm_content,
        #[Nullable, Numeric, Min(0)]
        public ?float $conversion_value,
        #[Nullable, BooleanType]
        public ?bool $is_converted,
        #[Nullable]
        public ?array $conversion_data
    )
    {
    }
    /**
     * Handle rules functionality with proper error handling.
     * @param ValidationContext $context
     * @return array
     */
    public static function rules(ValidationContext $context): array
    {
        return ['clicked_url' => ['nullable', new UrlRule(), 'max:500'], 'referer' => ['nullable', new UrlRule(), 'max:500']];
    }
}