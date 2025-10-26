<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PriceConfiguration
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? config('pricing', []);
    }

    public function currency(): string
    {
        return (string) Arr::get($this->config, 'currency', current_currency());
    }

    public function precision(): int
    {
        return (int) Arr::get($this->config, 'rounding.precision', 2);
    }

    public function roundingMode(): int
    {
        return (int) Arr::get($this->config, 'rounding.mode', PHP_ROUND_HALF_UP);
    }

    public function round(float $amount): float
    {
        return round($amount, $this->precision(), $this->roundingMode());
    }

    public function vatRate(): float
    {
        $value = $this->setting(
            Arr::get($this->config, 'vat.setting_key', 'tax_rate'),
            Arr::get($this->config, 'vat.rate', 21.0)
        );

        $rate = (float) $value;
        if ($rate > 1.0) {
            $rate /= 100;
        }

        return max(0.0, $rate);
    }

    public function shippingFlatRate(): float
    {
        $value = $this->setting(
            Arr::get($this->config, 'shipping.flat_rate_setting_key', 'shipping_cost'),
            Arr::get($this->config, 'shipping.flat_rate', 0.0)
        );

        return $this->round((float) $value);
    }

    public function freeShippingThreshold(): float
    {
        $value = $this->setting(
            Arr::get($this->config, 'shipping.free_threshold_setting_key', 'free_shipping_threshold'),
            Arr::get($this->config, 'shipping.free_threshold', 0.0)
        );

        return (float) $value;
    }

    private function setting(string $key, mixed $default): mixed
    {
        try {
            return app_setting($key, $default);
        } catch (Throwable $exception) {
            if (class_exists(Setting::class)) {
                Log::debug('Failed to read setting for pricing configuration', [
                    'key'       => $key,
                    'exception' => $exception->getMessage(),
                ]);
            }

            return $default;
        }
    }
}
