<?php

declare(strict_types=1);

namespace App\Support\Helpers;

final class SharedHelpers
{
    public static function formatPrice(float $amount, string $currency = 'EUR', ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $formatter = new \NumberFormatter(
            $locale === 'lt' ? 'lt_LT' : ($locale === 'de' ? 'de_DE' : 'en_US'),
            \NumberFormatter::CURRENCY
        );

        return $formatter->formatCurrency($amount, $currency);
    }

    public static function formatDate(\DateTimeInterface $date, ?string $format = null, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $format = $format ?? match ($locale) {
            'lt' => 'Y-m-d H:i',
            'de' => 'd.m.Y H:i',
            default => 'M j, Y g:i A',
        };

        return $date->format($format);
    }

    public static function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - mb_strlen($suffix)).$suffix;
    }

    public static function generateSlug(string $text, string $separator = '-'): string
    {
        // Convert to lowercase
        $text = mb_strtolower($text);

        // Replace Lithuanian characters
        $replacements = [
            'ą' => 'a', 'č' => 'c', 'ę' => 'e', 'ė' => 'e', 'į' => 'i',
            'š' => 's', 'ų' => 'u', 'ū' => 'u', 'ž' => 'z',
        ];

        $text = strtr($text, $replacements);

        // Remove special characters
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);

        // Replace spaces and multiple separators
        $text = preg_replace('/[\s-]+/', $separator, $text);

        return trim($text, $separator);
    }

    public static function getSeoTitle(string $title, ?string $siteName = null): string
    {
        $siteName = $siteName ?? config('app.name');

        return "{$title} - {$siteName}";
    }

    public static function getSeoDescription(string $content, int $length = 160): string
    {
        $cleaned = strip_tags($content);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return self::truncateText(trim($cleaned), $length);
    }

    public static function isRtlLocale(?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();

        return in_array($locale, ['ar', 'he', 'fa']);
    }

    public static function getLocalizedUrl(string $route, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $parameters['locale'] = $locale;

        return route($route, $parameters);
    }

    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isValidPhone(string $phone): bool
    {
        // Basic phone validation for Lithuanian numbers
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        return preg_match('/^(\+370|370|8)[0-9]{8}$/', $cleaned);
    }

    public static function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Format Lithuanian phone numbers
        if (preg_match('/^(\+370|370)([0-9]{8})$/', $cleaned, $matches)) {
            $number = $matches[2];

            return '+370 '.substr($number, 0, 3).' '.substr($number, 3, 2).' '.substr($number, 5);
        }

        if (preg_match('/^8([0-9]{8})$/', $cleaned, $matches)) {
            $number = $matches[1];

            return '+370 '.substr($number, 0, 3).' '.substr($number, 3, 2).' '.substr($number, 5);
        }

        return $phone;
    }
}
