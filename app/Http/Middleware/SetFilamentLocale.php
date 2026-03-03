<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

final class SetFilamentLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $isAdminContext = $this->isAdminContext($request);
        $locale = $this->resolveLocale($request, $isAdminContext);

        App::setLocale($locale);

        // Persist the selection so Filament keeps the same language across page loads.
        if ($isAdminContext) {
            Session::put('admin_locale', $locale);
        } else {
            Session::put('locale', $locale);
            cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));
        }

        Session::put('app.locale', $locale);

        return $next($request);
    }

    private function isAdminContext(Request $request): bool
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $routeName = (string) ($request->route()?->getName() ?? '');
        if ($routeName !== '' && str_starts_with($routeName, 'filament.admin.')) {
            return true;
        }

        $referer = (string) $request->headers->get('referer', '');

        return $referer !== '' && str_contains($referer, '/admin');
    }

    private function resolveLocale(Request $request, bool $isAdminContext): string
    {
        $supportedLocales = $this->supportedLocales();
        $fallbackLocale = $isAdminContext ? 'en' : (string) config('app.locale', 'lt');

        $requestedLocale = $request->query('locale', $request->input('locale'));
        $requestedLocale = is_string($requestedLocale) ? strtolower(trim($requestedLocale)) : '';

        $candidateLocales = array_filter([
            $requestedLocale !== '' ? $requestedLocale : null,
            $isAdminContext ? Session::get('admin_locale') : null,
            Session::get('locale'),
            Session::get('app.locale'),
            $request->cookie('app_locale'),
            ! $isAdminContext ? config('app.locale', 'lt') : null,
        ], static fn ($candidate): bool => is_string($candidate) && trim($candidate) !== '');

        foreach ($candidateLocales as $candidate) {
            $normalizedCandidate = strtolower(trim((string) $candidate));

            if (in_array($normalizedCandidate, $supportedLocales, true)) {
                return $normalizedCandidate;
            }
        }

        if (in_array($fallbackLocale, $supportedLocales, true)) {
            return $fallbackLocale;
        }

        return $supportedLocales[0] ?? 'en';
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $supported = config('app.supported_locales', ['lt', 'en']);

        $rawLocales = is_array($supported)
            ? $supported
            : explode(',', (string) $supported);

        $locales = array_values(array_unique(array_filter(array_map(
            static fn ($locale): ?string => is_string($locale) && trim($locale) !== ''
                ? strtolower(trim($locale))
                : null,
            $rawLocales
        ))));

        return $locales;
    }
}
