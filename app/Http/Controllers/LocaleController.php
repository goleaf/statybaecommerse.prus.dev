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
        // Collect the requested locale from either the route wildcard or the submitted payload.
        $requestedRaw = $locale ?? $request->input('locale');
        $requested = is_string($requestedRaw) ? $requestedRaw : null;

        // Resolve the configured list of supported locales and map the request to the best match.
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
            // Persist the locale-linked currency selection when one is configured.
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
            // Allow comma separated configuration strings while preserving order.
            $configured = array_map(trim(...), explode(',', $configured));
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

            // Keep the original casing to avoid breaking route constraints while ensuring uniqueness.
            $locales[] = $trimmed;
        }

        // Ensure downstream lookups operate on unique values without disturbing the configured precedence.
        return array_values(array_unique($locales));
    }

    /**
     * @param array<int, string> $supported
     */
    private function resolveLocale(?string $candidate, array $supported): string
    {
        if (is_string($candidate)) {
            // Normalise whitespace to avoid rejecting values that only differ by stray spaces.
            $candidate = trim($candidate);
        }

        if ($candidate !== null && $candidate !== '') {
            if (in_array($candidate, $supported, true)) {
                return $candidate;
            }

            // Accept regional variants (e.g., en-GB) by comparing their primary segments.
            $normalisedCandidate = strtolower(str_replace('_', '-', $candidate));
            foreach ($supported as $supportedLocale) {
                $normalisedSupported = strtolower(str_replace('_', '-', $supportedLocale));
                if ($normalisedSupported === $normalisedCandidate) {
                    return $supportedLocale;
                }

                // Extract the language segments while tolerating locales without regional suffixes.
                $supportedParts = explode('-', $normalisedSupported);
                $candidateParts = explode('-', $normalisedCandidate);
                $supportedSegment = (string) $supportedParts[0];
                $candidateSegment = (string) $candidateParts[0];

                if ($candidateSegment !== '' && $candidateSegment === $supportedSegment) {
                    return $supportedLocale;
                }
            }
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
            $targetPort = parse_url($target, PHP_URL_PORT);

            if ($targetHost === null || $targetScheme === null) {
                return false;
            }

            // Explicitly match the current host, scheme, and port to avoid open redirect exploits.
            if ($targetHost !== $request->getHost()) {
                return false;
            }

            if (strtolower((string) $targetScheme) !== strtolower($request->getScheme())) {
                return false;
            }

            $currentPort = $request->getPort();
            $normalisedTargetPort = $targetPort ?? ($targetScheme === 'https' ? 443 : 80);

            return $currentPort === $normalisedTargetPort;
        }

        if (Str::startsWith($target, ['//', 'javascript:', 'data:'])) {
            return false;
        }

        // Relative paths that begin with a forward slash stay within the application scope and are therefore safe.
        return Str::startsWith($target, '/');
    }
}
