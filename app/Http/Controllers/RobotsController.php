<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Stringable;

/**
 * RobotsController
 *
 * HTTP controller handling RobotsController related web requests, responses, and business logic with proper validation and error handling.
 */
class RobotsController extends Controller
{
    /**
     * Handle __invoke functionality with proper error handling.
     */
    public function __invoke(): Response
    {
        // Resolve the application URL so we can derive a stable host and scheme for the sitemap entries.
        $configuredAppUrl = config('app.url');

        // Safely coerce the application URL to a string because the configuration helper returns mixed values.
        if (is_string($configuredAppUrl)) {
            $appUrl = $configuredAppUrl;
        } elseif ($configuredAppUrl instanceof Stringable) {
            $appUrl = (string) $configuredAppUrl;
        } else {
            $appUrl = '';
        }

        // Extract the host, defaulting to the current request host when configuration is missing or malformed.
        $host = parse_url($appUrl, PHP_URL_HOST);
        $host = is_string($host) && $host !== '' ? $host : request()->getHost();

        // Extract the scheme, falling back to the incoming request scheme (or https) when necessary.
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        $scheme = is_string($scheme) && $scheme !== '' ? $scheme : (request()->getScheme() ?: 'https');

        // Normalise the supported locale list so both comma-separated strings and arrays are handled consistently.
        $supportedLocalesConfig = config('app.supported_locales', 'en');

        if ($supportedLocalesConfig instanceof Stringable) {
            $supportedLocalesConfig = (string) $supportedLocalesConfig;
        }

        if (! is_array($supportedLocalesConfig) && ! is_string($supportedLocalesConfig)) {
            $supportedLocalesConfig = '';
        }

        $locales = $this->normaliseLocales($supportedLocalesConfig);

        // Compose the robots.txt directives, including sitemaps for every enabled locale.
        $lines = [
            'User-agent: *',
            'Disallow: /cpanel/',
            'Disallow: /admin/',
            'Disallow: /horizon',
            'Disallow: /telescope',
        ];

        foreach ($locales as $locale) {
            $lines[] = sprintf('Sitemap: %s://%s/%s/sitemap.xml', $scheme, $host, $locale);
        }

        // Join the robots rules with Unix newlines and ensure a trailing newline as search engines expect.
        $content = implode(PHP_EOL, $lines) . PHP_EOL;

        // Return the plain-text response with the generated robots.txt body.
        return response($content, Response::HTTP_OK)->header('Content-Type', 'text/plain');
    }

    /**
     * Normalise the supported locales configuration value to a clean array of locale codes.
     *
     * @param  array<mixed>|Stringable|string $locales
     * @return array<int, string>
     */
    private function normaliseLocales(array|string|Stringable $locales): array
    {
        // Convert comma-separated strings into arrays for uniform processing.
        if (is_string($locales)) {
            $locales = explode(',', $locales);
        } elseif ($locales instanceof Stringable) {
            $locales = explode(',', (string) $locales);
        }

        /** @var array<int, mixed> $localeArray */
        $localeArray = $locales;

        // Ensure each locale is treated as a string before trimming and filtering.
        $normalisedLocales = array_map(
            static function ($locale): string {
                if ($locale instanceof Stringable) {
                    return (string) $locale;
                }

                if (is_scalar($locale)) {
                    return (string) $locale;
                }

                return '';
            },
            $localeArray,
        );

        // Trim whitespace, discard empty values, remove duplicates, and return the resulting list.
        return collect($normalisedLocales)
            ->map(static fn (string $locale): string => trim($locale))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
