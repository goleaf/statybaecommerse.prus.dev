<?php

declare(strict_types=1);

use App\Support\Security\CspNonce;

if (! function_exists('csp_nonce')) {
    /**
     * Resolve the current request's CSP nonce so Blade templates can opt-in to strict policies.
     */
    function csp_nonce(): string
    {
        return app(CspNonce::class)->value();
    }
}

if (! function_exists('app_setting')) {
    /**
     * Get or set a setting value.
     */
    function app_setting(string $key, mixed $default = null): mixed
    {
        $setting = \App\Models\Setting::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'integer' => (int) $setting->value,
            'float'   => (float) $setting->value,
            'array', 'json' => safe_json_decode_array($setting->value),
            default => $setting->value,
        };
    }
}

if (! function_exists('csp_nonce')) {
    function csp_nonce(): string
    {
        $request = app()->bound('request') ? request() : null;

        if ($request instanceof \Illuminate\Http\Request) {
            return \App\Support\Security\CspNonce::resolve($request);
        }

        $current = \App\Support\Security\CspNonce::current();
        if (is_string($current) && $current !== '') {
            return $current;
        }

        $nonce = \App\Support\Security\CspNonce::generate();
        \App\Support\Security\CspNonce::storeGlobally($nonce);

        return $nonce;
    }
}

// Removed legacy shopper_setting - use app_setting instead

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

if (! function_exists('current_currency')) {
    function current_currency(): string
    {
        // Per-request memoization to avoid duplicate DB lookups in lists
        static $resolved = null;

        // If a forced currency was set by locale mapping or user choice, honor it
        $forced = session('forced_currency');
        if (is_string($forced) && $forced !== '') {
            return $forced;
        }

        if ($resolved !== null) {
            return $resolved;
        }

        // During tests or before settings table exists, fallback safely without DB access
        if (Schema::hasTable('settings')) {
            try {
                $code = \App\Models\Setting::where('key', 'currency_code')->value('value');
                if (is_string($code) && $code !== '') {
                    return $resolved = $code;
                }
            } catch (\Throwable $e) {
                // ignore and continue to default
            }
        }

        // Default project currency
        return $resolved = 'EUR';
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
    function app_currency(): string
    {
        $code = (string) (config('app.currency', 'EUR'));
        if (Schema::hasTable('settings')) {
            try {
                $db = \App\Models\Setting::where('key', 'currency_code')->value('value');
                if (is_string($db) && $db !== '') {
                    return $db;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $code;
    }
}

if (! function_exists('format_money')) {
    function format_money(float|string|null $amount, ?string $currency = null, ?string $locale = null): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }
        $currency = $currency ?: current_currency();
        $locale = $locale ?: app()->getLocale();

        // Prefer Laravel's Number helper for currency formatting
        try {
            /** @var class-string|null $numberClass */
            $numberClass = \Illuminate\Support\Number::class;
            if (class_exists($numberClass)) {
                return \Illuminate\Support\Number::currency((float) $amount, $currency, $locale);
            }
        } catch (\Throwable $e) {
            // Fall back to intl formatter below
        }

        // Fallback: use intl NumberFormatter
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) $amount, $currency) ?: (string) $amount;
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
    function format_date(\DateTimeInterface|string|null $date, ?string $locale = null, int $dateType = \IntlDateFormatter::MEDIUM): string
    {
        if (! $date) {
            return '';
        }
        $dt = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);

        // Use year-month-day format for all locales
        return $dt->format(config('datetime.formats.date', 'Y-m-d'));
    }
}

// Removed legacy shopper_money_format - use app_money_format instead

if (! function_exists('format_datetime')) {
    function format_datetime(\DateTimeInterface|string|null $dateTime, ?string $locale = null): string
    {
        if (! $dateTime) {
            return '';
        }
        $dt = $dateTime instanceof \DateTimeInterface ? $dateTime : new \DateTime((string) $dateTime);

        // Use year-month-day format for all locales
        return $dt->format(config('datetime.formats.datetime', 'Y-m-d H:i'));
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
