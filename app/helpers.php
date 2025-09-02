<?php declare(strict_types=1);

use App\Actions\ZoneSessionManager;
use Illuminate\Support\Facades\Schema;

if (!function_exists('current_currency')) {
    function current_currency(): string
    {
        // If a forced currency was set by locale mapping or user choice, honor it
        $forced = session('forced_currency');
        if (is_string($forced) && $forced !== '') {
            return $forced;
        }

        if (ZoneSessionManager::checkSession()) {
            return ZoneSessionManager::getSession()->currencyCode;
        }

        // During tests or before Shopper migrations, fallback safely without DB access
        if (Schema::hasTable('sh_settings')) {
            try {
                $code = shopper_currency();
                if (is_string($code) && $code !== '') {
                    return $code;
                }
            } catch (\Throwable $e) {
                // ignore and continue to default
            }
        }

        // Default project currency
        return 'EUR';
    }
}

if (!function_exists('shopper_currency')) {
    function shopper_currency(): string
    {
        $code = (string) (config('app.currency', 'EUR'));
        if (Schema::hasTable('sh_settings')) {
            try {
                $db = DB::table('sh_settings')->where('key', 'currency_code')->value('value');
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

if (!function_exists('format_money')) {
    function format_money(float|string|null $amount, ?string $currency = null, ?string $locale = null): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }
        $currency = $currency ?: current_currency();
        $locale = $locale ?: app()->getLocale();
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency((float) $amount, $currency) ?: (string) $amount;
    }
}

if (!function_exists('shopper_money_format')) {
    function shopper_money_format(float|int|string $amount, ?string $currency = null): string
    {
        return format_money((float) $amount, $currency ?: current_currency());
    }
}

if (!function_exists('format_date')) {
    function format_date(\DateTimeInterface|string|null $date, ?string $locale = null, int $dateType = \IntlDateFormatter::MEDIUM): string
    {
        if (!$date) {
            return '';
        }
        $dt = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        $locale = $locale ?: app()->getLocale();
        $fmt = new \IntlDateFormatter($locale, $dateType, \IntlDateFormatter::NONE);
        return $fmt->format($dt);
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(\DateTimeInterface|string|null $dateTime): string
    {
        if (!$dateTime) {
            return '';
        }
        $dt = $dateTime instanceof \DateTimeInterface ? $dateTime : new \DateTime((string) $dateTime);
        return $dt->format('Y-m-d H:i');
    }
}

if (!function_exists('shopper_feature_enabled')) {
    function shopper_feature_enabled(string $featureName): bool
    {
        $feature = config('shopper.features.' . $featureName);
        if ($feature instanceof \App\Support\FeatureState) {
            return $feature === \App\Support\FeatureState::Enabled;
        }
        if (is_string($feature)) {
            return strtolower($feature) === strtolower(\App\Support\FeatureState::Enabled->value);
        }
        return (bool) $feature;
    }
}

if (!function_exists('debug_discount')) {
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

if (!function_exists('debug_translation')) {
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

if (!function_exists('debug_livewire')) {
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

if (!function_exists('debug_cart')) {
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

if (!function_exists('debug_order')) {
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

if (!function_exists('shopper_table')) {
    function shopper_table(string $name): string
    {
        $prefix = (string) config('shopper.core.table_prefix', 'sh_');
        return $prefix . $name;
    }
}

if (!function_exists('shopper_panel_assets')) {
    function shopper_panel_assets(string $asset): string
    {
        return url(shopper()->prefix() . $asset);
    }
}

if (!function_exists('shopper_fallback_url')) {
    function shopper_fallback_url(): string
    {
        return shopper_panel_assets('/images/placeholder.jpg');
    }
}

if (!function_exists('shopper')) {
    function shopper(): \Shopper\ShopperPanel
    {
        static $panel = null;
        if ($panel === null) {
            $panel = new \Shopper\ShopperPanel(prefix: (string) config('shopper.core.prefix', '/admin'));
        }
        return $panel;
    }
}
