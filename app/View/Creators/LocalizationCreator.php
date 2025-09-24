<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\Shared\TranslationService;
use Illuminate\Contracts\View\View;

/**
 * LocalizationCreator
 *
 * View Creator that provides localization data to views.
 * This includes translations, locale-specific formatting, and regional settings.
 */
final class LocalizationCreator
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        $locale = app()->getLocale();
        $currency = current_currency();

        $view->with([
            // Locale information
            'locale' => $locale,
            'currency' => $currency,
            'localeName' => $this->getLocaleName($locale),
            'currencySymbol' => $this->getCurrencySymbol($currency),

            // Translation helpers
            'trans' => fn (string $key, array $replace = []) => $this->translationService->getTranslation($key, $locale, $replace),
            'transChoice' => fn (string $key, int $number, array $replace = []) => $this->translationService->getTranslationChoice($key, $number, $locale, $replace),

            // Formatting helpers
            'formatPrice' => fn (float $amount) => $this->formatPrice($amount, $currency),
            'formatDate' => fn ($date, ?string $format = null) => $this->formatDate($date, $format, $locale),
            'formatNumber' => fn (float $number, int $decimals = 2) => $this->formatNumber($number, $decimals, $locale),

            // Regional settings
            'dateFormat' => $this->getDateFormat($locale),
            'timeFormat' => $this->getTimeFormat($locale),
            'numberFormat' => $this->getNumberFormat($locale),
            'currencyFormat' => $this->getCurrencyFormat($currency),

            // Language direction
            'isRTL' => $this->isRightToLeft($locale),
            'textDirection' => $this->getTextDirection($locale),
        ]);
    }

    /**
     * Get locale display name.
     */
    private function getLocaleName(string $locale): string
    {
        return match ($locale) {
            'lt' => 'Lietuvių',
            'en' => 'English',
            'ru' => 'Русский',
            'de' => 'Deutsch',
            default => ucfirst($locale),
        };
    }

    /**
     * Get currency symbol.
     */
    private function getCurrencySymbol(string $currency): string
    {
        return match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'PLN' => 'zł',
            default => $currency,
        };
    }

    /**
     * Format price with currency.
     */
    private function formatPrice(float $amount, string $currency): string
    {
        $symbol = $this->getCurrencySymbol($currency);
        $formatted = number_format($amount, 2, ',', ' ');

        return match ($currency) {
            'EUR' => "{$formatted} {$symbol}",
            'USD' => "{$symbol}{$formatted}",
            'GBP' => "{$symbol}{$formatted}",
            default => "{$formatted} {$currency}",
        };
    }

    /**
     * Format date according to locale.
     */
    private function formatDate($date, ?string $format, string $locale): string
    {
        if (! $date) {
            return '';
        }

        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        if (! $format) {
            $format = $this->getDateFormat($locale);
        }

        return $date->locale($locale)->translatedFormat($format);
    }

    /**
     * Format number according to locale.
     */
    private function formatNumber(float $number, int $decimals, string $locale): string
    {
        return match ($locale) {
            'lt' => number_format($number, $decimals, ',', ' '),
            'en' => number_format($number, $decimals, '.', ','),
            'de' => number_format($number, $decimals, ',', '.'),
            default => number_format($number, $decimals, '.', ','),
        };
    }

    /**
     * Get date format for locale.
     */
    private function getDateFormat(string $locale): string
    {
        return match ($locale) {
            'lt' => 'Y-m-d',
            'en' => 'M j, Y',
            'de' => 'd.m.Y',
            default => 'Y-m-d',
        };
    }

    /**
     * Get time format for locale.
     */
    private function getTimeFormat(string $locale): string
    {
        return match ($locale) {
            'lt' => 'H:i',
            'en' => 'g:i A',
            'de' => 'H:i',
            default => 'H:i',
        };
    }

    /**
     * Get number format for locale.
     */
    private function getNumberFormat(string $locale): array
    {
        return match ($locale) {
            'lt' => ['decimal' => ',', 'thousands' => ' '],
            'en' => ['decimal' => '.', 'thousands' => ','],
            'de' => ['decimal' => ',', 'thousands' => '.'],
            default => ['decimal' => '.', 'thousands' => ','],
        };
    }

    /**
     * Get currency format for currency.
     */
    private function getCurrencyFormat(string $currency): array
    {
        return match ($currency) {
            'EUR' => ['symbol' => '€', 'position' => 'after', 'space' => true],
            'USD' => ['symbol' => '$', 'position' => 'before', 'space' => false],
            'GBP' => ['symbol' => '£', 'position' => 'before', 'space' => false],
            default => ['symbol' => $currency, 'position' => 'after', 'space' => true],
        };
    }

    /**
     * Check if locale is right-to-left.
     */
    private function isRightToLeft(string $locale): bool
    {
        return in_array($locale, ['ar', 'he', 'fa', 'ur']);
    }

    /**
     * Get text direction for locale.
     */
    private function getTextDirection(string $locale): string
    {
        return $this->isRightToLeft($locale) ? 'rtl' : 'ltr';
    }
}
