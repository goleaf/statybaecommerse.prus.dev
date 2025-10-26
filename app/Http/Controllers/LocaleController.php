<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final class LocaleController
{
    public function __invoke(Request $request, ?string $locale = null): RedirectResponse
    {
        $requestedRaw = $locale ?? $request->input('locale');
        $requested = is_string($requestedRaw) ? $requestedRaw : null;
        $supported = $this->supportedLocales();
        $resolved = $this->resolveLocale($requested, $supported);

        App::setLocale($resolved);
        app()->instance('request_locale', $resolved);

        Session::put('locale', $resolved);
        Session::put('app.locale', $resolved);

        cookie()->queue(cookie('app_locale', $resolved, 60 * 24 * 30));

        $mappingConfig = config('app.locale_mapping', []);
        $mapping = is_array($mappingConfig) ? $mappingConfig : [];
        $localeMapping = $mapping[$resolved] ?? null;
        if (is_array($localeMapping)) {
            $currency = $localeMapping['currency'] ?? null;
            if (is_string($currency) && $currency !== '') {
                Session::put('forced_currency', $currency);
            }
        }

        $user = Auth::user();
        if ($user instanceof User && $user->preferred_locale !== $resolved) {
            $user->forceFill(['preferred_locale' => $resolved])->save();
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $this->isSafeRedirect($redirectTo, $request)) {
            return redirect()->to($redirectTo);
        }

        return redirect()->back(fallback: $this->fallbackRedirect($supported));
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $configured = config('app.supported_locales', []);

        if (is_string($configured)) {
            $configured = array_map('trim', explode(',', $configured));
        }

        if (! is_array($configured)) {
            return [];
        }

        $locales = [];

        foreach ($configured as $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }

            $locales[] = $trimmed;
        }

        return $locales;
    }

    /**
     * @param array<int, string> $supported
     */
    private function resolveLocale(?string $candidate, array $supported): string
    {
        if (is_string($candidate)) {
            $candidate = trim($candidate);
        }

        if (is_string($candidate) && $candidate !== '' && in_array($candidate, $supported, true)) {
            return $candidate;
        }

        $fallback = $this->preferredFallbackLocale($supported);

        return $fallback ?? $this->applicationLocale();
    }

    /**
     * @param array<int, string> $supported
     */
    private function preferredFallbackLocale(array $supported): ?string
    {
        $fallbackRaw = config('app.fallback_locale');
        $fallback = is_string($fallbackRaw) ? trim($fallbackRaw) : '';
        if ($fallback !== '' && in_array($fallback, $supported, true)) {
            return $fallback;
        }

        $default = $this->applicationLocale();
        if ($default !== '' && in_array($default, $supported, true)) {
            return $default;
        }

        return $supported[0] ?? null;
    }

    /**
     * @param array<int, string> $supported
     */
    private function fallbackRedirect(array $supported): string
    {
        $locale = $this->preferredFallbackLocale($supported) ?? $this->applicationLocale();
        if ($locale === '') {
            $locale = 'en';
        }

        if (Route::has('localized.home')) {
            return route('localized.home', ['locale' => $locale]);
        }

        if (Route::has('home')) {
            return route('home');
        }

        return url('/');
    }

    private function applicationLocale(): string
    {
        $configured = config('app.locale');

        if (is_string($configured)) {
            $trimmed = trim($configured);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return 'en';
    }

    private function isSafeRedirect(string $target, Request $request): bool
    {
        $target = trim($target);
        if ($target === '') {
            return false;
        }

        if (Str::startsWith($target, ['http://', 'https://'])) {
            $targetHost = parse_url($target, PHP_URL_HOST);
            $targetScheme = parse_url($target, PHP_URL_SCHEME);

            if ($targetHost === null || $targetScheme === null) {
                return false;
            }

            return $targetHost === $request->getHost() && $targetScheme === $request->getScheme();
        }

        if (Str::startsWith($target, ['//', 'javascript:', 'data:'])) {
            return false;
        }

        return Str::startsWith($target, '/');
    }
}
