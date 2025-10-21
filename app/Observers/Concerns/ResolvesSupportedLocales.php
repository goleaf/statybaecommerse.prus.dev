<?php

declare(strict_types=1);

namespace App\Observers\Concerns;

trait ResolvesSupportedLocales
{
    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $configured = config('app.supported_locales');

        if (is_string($configured)) {
            $configured = array_map('trim', explode(',', $configured));
        }

        if (! is_array($configured)) {
            $configured = [];
        }

        $configured[] = config('app.locale');
        $configured[] = app()->getLocale();

        $normalized = [];

        foreach ($configured as $locale) {
            if (! is_string($locale)) {
                continue;
            }

            $locale = trim($locale);

            if ($locale === '') {
                continue;
            }

            $normalized[$locale] = $locale;
        }

        if ($normalized === []) {
            $fallback = config('app.fallback_locale', 'en');

            if (is_string($fallback) && $fallback !== '') {
                $normalized[$fallback] = $fallback;
            } else {
                $normalized['en'] = 'en';
            }
        }

        return array_values($normalized);
    }
}
