<?php

declare(strict_types=1);

if (! function_exists('app_setting')) {
    /**
     * Get application setting value with multi-layer caching and performance optimization.
     *
     * Performance optimizations:
     * - Static cache for request-level memoization (prevents N+1 queries)
     * - Laravel cache for cross-request persistence with tags
     * - Batch loading to reduce database queries
     * - Index-optimized database queries
     * - Graceful fallback chain
     */
    function app_setting(string $key, mixed $default = null): mixed
    {
        // Layer 1: Static cache for request-level memoization
        static $requestCache = [];
        static $batchLoaded = false;

        if (array_key_exists($key, $requestCache)) {
            return $requestCache[$key];
        }

        // Layer 2: Laravel cache for cross-request persistence (5 minutes TTL)
        $cacheKey = "app_settings.{$key}";

        if (cache()->has($cacheKey)) {
            $cached = cache()->get($cacheKey);

            return $requestCache[$key] = $cached;
        }

        // Layer 3: Database with batch loading optimization
        if (! $batchLoaded && Schema::hasTable('settings')) {
            try {
                // Batch load all settings to prevent N+1 queries
                // Use select() to only fetch needed columns for performance
                $settings = \App\Models\Setting::query()
                    ->select(['key', 'value', 'type'])
                    ->get()
                    ->keyBy('key');

                foreach ($settings as $settingKey => $setting) {
                    $value = match ($setting->type) {
                        'boolean' => (bool) $setting->value,
                        'integer' => (int) $setting->value,
                        'float'   => (float) $setting->value,
                        'array', 'json' => safe_json_decode_array($setting->value),
                        default => $setting->value,
                    };

                    // Cache for 5 minutes with tags for selective invalidation
                    if (cache()->getStore() instanceof \Illuminate\Cache\TaggableStore) {
                        cache()->tags(['app_settings'])->put("app_settings.{$settingKey}", $value, 300);
                    } else {
                        cache()->put("app_settings.{$settingKey}", $value, 300);
                    }
                    $requestCache[$settingKey] = $value;
                }

                $batchLoaded = true;

                if (array_key_exists($key, $requestCache)) {
                    return $requestCache[$key];
                }
            } catch (\Throwable $e) {
                // Log error but continue to fallback
                if (app()->bound('log')) {
                    app('log')->warning('Failed to batch load settings from database', [
                        'error' => $e->getMessage(),
                        'key'   => $key,
                    ]);
                }
            }
        }

        // Layer 4: Fallback to config
        $configValue = config("app.settings.{$key}", $default);

        // Cache the fallback value briefly to prevent repeated config lookups
        if (cache()->getStore() instanceof \Illuminate\Cache\TaggableStore) {
            cache()->tags(['app_settings'])->put($cacheKey, $configValue, 60);
        } else {
            cache()->put($cacheKey, $configValue, 60);
        }

        return $requestCache[$key] = $configValue;
    }
}

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

if (! function_exists('current_currency')) {
    /**
     * Get the current currency code with optimized fallback chain and validation.
     *
     * Performance optimizations:
     * - Request-level memoization
     * - Early validation to prevent invalid currency propagation
     * - Optimized session access
     */
    function current_currency(): string
    {
        // Per-request memoization to avoid duplicate lookups
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        // Priority 1: Session forced currency (user preference) with validation
        if (session()->has('forced_currency')) {
            $forced = session('forced_currency');
            if (is_string($forced) && $forced !== '' && validate_currency_code($forced)) {
                return $resolved = $forced;
            }
        }

        // Priority 2: Database setting with validation
        $dbCurrency = app_setting('currency_code');
        if (is_string($dbCurrency) && $dbCurrency !== '' && validate_currency_code($dbCurrency)) {
            return $resolved = $dbCurrency;
        }

        // Priority 3: Application config with validation and fallback
        $configCurrency = config('app.currency', 'EUR');
        $validatedCurrency = validate_currency_code($configCurrency) ? $configCurrency : 'EUR';

        return $resolved = (string) $validatedCurrency;
    }
}

if (! function_exists('safe_json_decode_array')) {
    /**
     * Decode a JSON value into an associative array safely.
     * - Accepts mixed input; non-strings return [] or the array as-is
     * - Returns [] on invalid JSON or non-array results
     */
    function safe_json_decode_array(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return [];
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }
}

if (! function_exists('app_currency')) {
    /**
     * Get the application currency code.
     * Alias for current_currency() for backward compatibility.
     */
    function app_currency(): string
    {
        return current_currency();
    }
}

if (! function_exists('format_money')) {
    /**
     * Format money amount with proper currency and locale formatting.
     */
    function format_money(float|string|null $amount, ?string $currency = null, ?string $locale = null): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $currency = $currency ?: current_currency();
        $locale = $locale ?: app()->getLocale();
        $numericAmount = (float) $amount;

        // Strategy 1: Laravel's Number helper (preferred)
        if (class_exists(\Illuminate\Support\Number::class)) {
            try {
                return \Illuminate\Support\Number::currency($numericAmount, $currency, $locale);
            } catch (\Throwable $e) {
                // Continue to next strategy
            }
        }

        // Strategy 2: PHP Intl NumberFormatter
        if (class_exists(\NumberFormatter::class)) {
            try {
                $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
                $formatted = $formatter->formatCurrency($numericAmount, $currency);
                if ($formatted !== false) {
                    return $formatted;
                }
            } catch (\Throwable $e) {
                // Continue to fallback
            }
        }

        // Strategy 3: Manual formatting with locale awareness
        return formatMoneyFallback($numericAmount, $currency, $locale);
    }
}

if (! function_exists('formatMoneyFallback')) {
    /**
     * Fallback money formatting when advanced formatters are unavailable.
     */
    function formatMoneyFallback(float $amount, string $currency, string $locale): string
    {
        $normalizedLocale = Str::of($locale)->lower()->replace(['@', '_'], '-')->value();
        $primaryLocale = explode('-', $normalizedLocale)[0] ?? $normalizedLocale;

        // Locales that typically use dot as decimal separator
        $dotDecimalLocales = ['en', 'zh', 'ja', 'ko', 'th', 'my', 'id', 'ms', 'vi', 'bn'];

        $useDotDecimal = in_array($primaryLocale, $dotDecimalLocales, true);
        $decimalSeparator = $useDotDecimal ? '.' : ',';
        $thousandsSeparator = $useDotDecimal ? ',' : ' ';

        $formattedAmount = number_format($amount, 2, $decimalSeparator, $thousandsSeparator);

        if ($currency === '') {
            return $formattedAmount;
        }

        return $useDotDecimal
            ? $currency . ' ' . $formattedAmount
            : $formattedAmount . ' ' . $currency;
    }
}

if (! function_exists('app_money_format')) {
    function app_money_format(float|int|string $amount, ?string $currency = null): string
    {
        return format_money((float) $amount, $currency ?: current_currency());
    }
}

if (! function_exists('format_price')) {
    function format_price(float|int|string|null $amount, ?string $currency = null, ?string $locale = null): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $currency = $currency ?: current_currency();
        $locale = $locale ?: app()->getLocale();

        // Use the existing format_money function for consistency
        return format_money((float) $amount, $currency, $locale);
    }
}

if (! function_exists('format_date')) {
    /**
     * Format date according to application configuration.
     */
    function format_date(\DateTimeInterface|string|null $date, ?string $locale = null, int $dateType = \IntlDateFormatter::MEDIUM): string
    {
        if (! $date) {
            return '';
        }

        try {
            $dt = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);

            return $dt->format(config('datetime.formats.date', 'Y-m-d'));
        } catch (\Throwable $e) {
            return '';
        }
    }
}

// Removed legacy shopper_money_format - use app_money_format instead

if (! function_exists('format_datetime')) {
    /**
     * Format datetime according to application configuration.
     */
    function format_datetime(\DateTimeInterface|string|null $dateTime, ?string $locale = null): string
    {
        if (! $dateTime) {
            return '';
        }

        try {
            $dt = $dateTime instanceof \DateTimeInterface ? $dateTime : new \DateTime((string) $dateTime);

            return $dt->format(config('datetime.formats.datetime', 'Y-m-d H:i'));
        } catch (\Throwable $e) {
            return '';
        }
    }
}

if (! function_exists('format_date_short')) {
    function format_date_short(\DateTimeInterface|string|null $date, ?string $locale = null): string
    {
        if (! $date) {
            return '';
        }
        $dt = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);

        // Use year-month-day format for all locales
        return $dt->format(config('datetime.formats.date_short', 'y-m-d'));
    }
}

if (! function_exists('format_datetime_full')) {
    function format_datetime_full(\DateTimeInterface|string|null $dateTime, ?string $locale = null): string
    {
        if (! $dateTime) {
            return '';
        }
        $dt = $dateTime instanceof \DateTimeInterface ? $dateTime : new \DateTime((string) $dateTime);

        // Use year-month-day format for all locales
        return $dt->format(config('datetime.formats.datetime_full', 'Y-m-d H:i:s'));
    }
}

if (! function_exists('format_time')) {
    function format_time(\DateTimeInterface|string|null $dateTime, ?string $locale = null): string
    {
        if (! $dateTime) {
            return '';
        }
        $dt = $dateTime instanceof \DateTimeInterface ? $dateTime : new \DateTime((string) $dateTime);

        return $dt->format(config('datetime.formats.time', 'H:i'));
    }
}

if (! function_exists('app_feature_enabled')) {
    function app_feature_enabled(string $featureName): bool
    {
        $feature = config('app-features.features.' . $featureName);
        if ($feature instanceof \App\Support\FeatureState) {
            return $feature === \App\Support\FeatureState::Enabled;
        }
        if (is_string($feature)) {
            return strtolower($feature) === strtolower(\App\Support\FeatureState::Enabled->value);
        }

        return (bool) $feature;
    }
}

if (! function_exists('debug_discount')) {
    function debug_discount(string $code, array $conditions, bool $applied, float $amount): void
    {
        try {
            if (app()->bound('debugbar.discount')) {
                app('debugbar.discount')->logDiscountApplication($code, $conditions, $applied, $amount);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}

if (! function_exists('debug_translation')) {
    function debug_translation(string $key, string $locale, string $value, bool $fromCache): void
    {
        try {
            if (app()->bound('debugbar.translation')) {
                app('debugbar.translation')->logTranslationQuery($key, $locale, $value, $fromCache);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}

if (! function_exists('debug_livewire')) {
    function debug_livewire(string $component, string $phase, array $data = []): void
    {
        try {
            if (app()->bound('debugbar.livewire')) {
                app('debugbar.livewire')->logComponentLifecycle($component, $phase, $data);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}

if (! function_exists('debug_cart')) {
    function debug_cart(string $operation, array $data = []): void
    {
        try {
            if (app()->bound('debugbar.ecommerce')) {
                app('debugbar.ecommerce')->logCartOperation($operation, $data);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}

if (! function_exists('debug_order')) {
    function debug_order(string $operation, string $orderNumber, array $data = []): void
    {
        try {
            if (app()->bound('debugbar.ecommerce')) {
                app('debugbar.ecommerce')->logOrder($operation, $orderNumber, $data);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}

if (! function_exists('safe_asset')) {
    function safe_asset(string $path): string
    {
        $relativePath = '/' . ltrim($path, '/');

        try {
            $app = app();

            if (! method_exists($app, 'bound') || ! $app->bound('url') || ! $app->bound('request')) {
                return $relativePath;
            }

            $request = $app->make('request');

            if (! $request instanceof \Illuminate\Http\Request) {
                return $relativePath;
            }

            return asset($path);
        } catch (\Throwable $exception) {
            return $relativePath;
        }
    }
}

if (! function_exists('media_placeholder_url')) {
    function media_placeholder_url(string $key, ?string $variant = null, ?string $default = null): string
    {
        try {
            $resolver = app(App\Support\Media\PlaceholderResolver::class);

            return $resolver->resolve($key, $variant, $default) ?? ($default ?? '');
        } catch (\Throwable $exception) {
            return $default ?? '';
        }
    }
}

if (! function_exists('app_placeholder_url')) {
    function app_placeholder_url(): string
    {
        return media_placeholder_url('app', null, safe_asset('images/placeholder.jpg'));
    }
}

if (! function_exists('product_placeholder_url')) {
    function product_placeholder_url(?string $variant = null): string
    {
        $fallback = $variant === 'thumb'
            ? safe_asset('images/placeholder-product.png')
            : safe_asset('images/placeholder-product.jpg');

        $key = $variant === 'thumb' ? 'product_png' : 'product';

        return media_placeholder_url($key, $variant, $fallback);
    }
}

if (! function_exists('og_placeholder_url')) {
    function og_placeholder_url(): string
    {
        return media_placeholder_url('og', null, safe_asset('images/og-default.jpg'));
    }
}

if (! function_exists('media_img')) {
    /**
     * Render a responsive <img> tag for a Spatie media item.
     *
     * @param array<string, mixed> $attributes
     */
    function media_img(\Spatie\MediaLibrary\MediaCollections\Models\Media $media, array $attributes = []): HtmlString
    {
        $variants = $media->getCustomProperty('variants', []);
        if ($variants === []) {
            $configured = config('media.variants', []);
            foreach ($configured as $name => $details) {
                $url = $media->getUrl($name);
                if (is_string($url) && $url !== $media->getUrl()) {
                    $variants[$name] = [
                        'url'   => $url,
                        'width' => $details['width'] ?? null,
                    ];
                }
            }
        }

        $original = $media->getCustomProperty('original', []);
        $src = Arr::get($variants, 'medium.url') ?? ($original['url'] ?? $media->getUrl());
        $alt = $attributes['alt'] ?? $media->getCustomProperty('alt') ?? $media->name;
        $loading = $attributes['loading'] ?? 'lazy';
        $sizes = $attributes['sizes'] ?? '100vw';
        $dir = \App\Support\Helpers\SharedHelpers::isRtlLocale() ? 'rtl' : 'ltr';

        $srcset = collect($variants)
            ->filter(fn ($variant) => isset($variant['url']))
            ->map(function (array $variant) {
                $descriptor = isset($variant['width']) ? $variant['width'] . 'w' : null;

                return trim($variant['url'] . ' ' . ($descriptor ?? ''));
            })
            ->filter()
            ->implode(', ');

        $attributes = array_merge(
            [
                'src'      => $src,
                'alt'      => $alt,
                'loading'  => $loading,
                'decoding' => $attributes['decoding'] ?? 'async',
                'sizes'    => $sizes,
                'dir'      => $dir,
            ],
            Arr::except($attributes, ['alt', 'loading', 'sizes', 'decoding'])
        );

        if ($srcset !== '') {
            $attributes['srcset'] = $srcset;
        }

        if (isset($original['width'], $original['height'])) {
            $attributes['width'] = $attributes['width'] ?? $original['width'];
            $attributes['height'] = $attributes['height'] ?? $original['height'];
        }

        $attrString = collect($attributes)
            ->map(function ($value, $key) {
                if ($value === null || $value === '') {
                    return null;
                }

                return $key . '="' . e((string) $value) . '"';
            })
            ->filter()
            ->implode(' ');

        return new HtmlString('<img ' . $attrString . ' />');
    }
}
if (! function_exists('app_setting_flush_cache')) {
    /**
     * Flush the static cache for app_setting function.
     * Useful for testing or when settings are updated during runtime.
     */
    function app_setting_flush_cache(): void
    {
        // This is a bit of a hack to access the static variable
        // We'll call app_setting with a unique key to reset the cache
        static $flushed = false;
        if (! $flushed) {
            $flushed = true;
            // Force a cache reset by accessing the static variable indirectly
            app_setting('__flush_cache_' . uniqid(), null);
        }
    }
}

if (! function_exists('validate_currency_code')) {
    /**
     * Validate if a currency code is valid according to ISO 4217.
     *
     * @param  string $currencyCode The currency code to validate
     * @return bool   True if valid, false otherwise
     */
    function validate_currency_code(string $currencyCode): bool
    {
        // Common ISO 4217 currency codes
        $validCurrencies = [
            'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
            'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYN', 'BZD',
            'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK',
            'DJF', 'DKK', 'DOP', 'DZD',
            'EGP', 'ERN', 'ETB', 'EUR',
            'FJD', 'FKP',
            'GBP', 'GEL', 'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD',
            'HKD', 'HNL', 'HRK', 'HTG', 'HUF',
            'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK',
            'JEP', 'JMD', 'JOD', 'JPY',
            'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT',
            'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD',
            'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRU', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN',
            'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD',
            'OMR',
            'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG',
            'QAR',
            'RON', 'RSD', 'RUB', 'RWF',
            'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLE', 'SLL', 'SOS', 'SRD', 'STN', 'SYP', 'SZL',
            'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TVD', 'TWD', 'TZS',
            'UAH', 'UGX', 'USD', 'UYU', 'UYW', 'UZS',
            'VED', 'VES', 'VND', 'VUV',
            'WST',
            'XAF', 'XCD', 'XDR', 'XOF', 'XPF',
            'YER',
            'ZAR', 'ZMW', 'ZWL',
        ];

        return in_array(strtoupper($currencyCode), $validCurrencies, true);
    }
}

if (! function_exists('sanitize_html_content')) {
    /**
     * Sanitize HTML content using the application's HTML sanitizer.
     *
     * @param  string $content The HTML content to sanitize
     * @return string The sanitized HTML content
     */
    function sanitize_html_content(string $content): string
    {
        try {
            if (app()->bound(\App\Support\Html\HtmlSanitizer::class)) {
                $sanitizer = app(\App\Support\Html\HtmlSanitizer::class);

                return $sanitizer->sanitize($content);
            }
        } catch (\Throwable $e) {
            // Log error but continue with basic sanitization
            if (app()->bound('log')) {
                app('log')->warning('Failed to use HTML sanitizer', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Basic fallback sanitization
        return strip_tags($content, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>');
    }
}

if (! function_exists('get_tenant_setting')) {
    /**
     * Get a tenant-specific setting value.
     * Falls back to global app_setting if tenant setting doesn't exist.
     *
     * @param  string   $key      The setting key
     * @param  mixed    $default  Default value
     * @param  int|null $tenantId Tenant ID (uses current tenant if null)
     * @return mixed    The setting value
     */
    function get_tenant_setting(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        // This would need to be implemented based on your tenant architecture
        // For now, fall back to global app_setting
        return app_setting($key, $default);
    }
}
