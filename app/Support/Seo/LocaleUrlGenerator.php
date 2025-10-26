<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Throwable;

final class LocaleUrlGenerator
{
    /**
     * @var array<int, string>
     */
    private array $supportedLocales;

    public function __construct()
    {
        $configured = config('app.supported_locales', []);
        $locales = is_string($configured) ? explode(',', (string) $configured) : $configured;
        $this->supportedLocales = collect($locales)
            ->map(static fn ($locale) => trim((string) $locale))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function supportedLocales(): array
    {
        return $this->supportedLocales;
    }

    public function localizedRoute(string $routeName, array $parameters, string $locale): ?string
    {
        if (! Route::has($routeName)) {
            return null;
        }

        $resolvedParameters = array_merge($parameters, ['locale' => $locale]);

        try {
            return route($routeName, $resolvedParameters);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  callable(string): (array|null) $parameterResolver
     * @return array<string, string>
     */
    public function generateAlternates(string $routeName, callable $parameterResolver): array
    {
        $links = [];

        foreach ($this->supportedLocales() as $locale) {
            $parameters = $parameterResolver($locale);
            if (! is_array($parameters)) {
                continue;
            }

            $url = $this->localizedRoute($routeName, $parameters, $locale);
            if ($url) {
                $links[$locale] = $url;
            }
        }

        return $this->appendDefaultLocale($links);
    }

    /**
     * @return array<string, string>
     */
    public function fallbackAlternateLocales(?string $path = null, ?string $query = null): array
    {
        $currentUrl = url()->full();
        $path ??= parse_url($currentUrl, PHP_URL_PATH) ?: '/';
        $query ??= parse_url($currentUrl, PHP_URL_QUERY) ?: '';

        $segments = array_values(array_filter(explode('/', ltrim((string) $path, '/'))));
        if (isset($segments[0]) && in_array($segments[0], $this->supportedLocales(), true)) {
            array_shift($segments);
        }

        $rest = trim(implode('/', $segments), '/');
        $queryString = $query !== '' ? '?' . $query : '';

        $links = [];
        foreach ($this->supportedLocales() as $locale) {
            $url = $rest === '' ? url('/' . $locale) : url('/' . $locale . '/' . $rest);
            $links[$locale] = $url . $queryString;
        }

        $fallbackUrl = ($rest === '' ? url('/') : url('/' . $rest)) . $queryString;

        return $this->appendDefaultLocale($links, $fallbackUrl);
    }

    public function translatedValue(
        object $model,
        string $locale,
        string $method = 'getTranslatedSlug',
        string $translationField = 'slug',
        ?string $fallbackProperty = 'slug'
    ): ?string {
        if ($method !== '' && method_exists($model, $method)) {
            $value = $model->{$method}($locale);
            if ($this->isFilled($value)) {
                return $value;
            }
        }

        if (method_exists($model, 'trans')) {
            $value = $model->trans($translationField, $locale);
            if ($this->isFilled($value)) {
                return $value;
            }
        }

        if ($fallbackProperty && isset($model->{$fallbackProperty}) && $this->isFilled($model->{$fallbackProperty})) {
            return (string) $model->{$fallbackProperty};
        }

        return null;
    }

    /**
     * @param  array<string, string> $links
     * @return array<string, string>
     */
    private function appendDefaultLocale(array $links, ?string $fallback = null): array
    {
        if ($links === []) {
            return $links;
        }

        $defaultLocale = config('app.fallback_locale', config('app.locale', 'en'));
        $defaultUrl = $links[$defaultLocale] ?? Arr::first($links) ?? $fallback;

        if ($defaultUrl && ! array_key_exists('x-default', $links)) {
            $links['x-default'] = $defaultUrl;
        }

        return $links;
    }

    private function isFilled(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null;
    }
}
