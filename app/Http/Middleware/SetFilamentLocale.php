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
        $this->enforceSingleLocaleConfiguration();

        if (! $isAdminContext) {
            $routeLocale = strtolower((string) $request->route('locale', ''));
            $routeName = (string) ($request->route()?->getName() ?? '');

            if (
                $routeLocale !== ''
                && $routeName !== 'language.switch'
                && ! str_starts_with($routeName, 'admin.')
            ) {
                return redirect()->to($this->stripRouteLocale($request), 301);
            }
        }

        $locale = $this->singleLocale();

        App::setLocale($locale);
        Session::put('admin_locale', $locale);
        Session::put('locale', $locale);
        Session::put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));

        return $next($request);
    }

    private function isAdminContext(Request $request): bool
    {
        $context = strtolower(trim((string) $request->input('context', '')));
        if ($context === 'admin') {
            return true;
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        $routeName = (string) ($request->route()?->getName() ?? '');
        if ($routeName !== '' && str_starts_with($routeName, 'filament.admin.')) {
            return true;
        }

        $referer = (string) $request->headers->get('referer', '');
        if ($referer === '' || ! str_contains($referer, '/admin')) {
            return false;
        }

        return in_array($routeName, ['language.switch', 'locale.switch'], true)
            || $request->is('lang/*')
            || $request->is('locale');
    }

    private function singleLocale(): string
    {
        $configured = strtolower(trim((string) config('shared.localization.default_locale', 'lt')));

        return $configured !== '' ? $configured : 'lt';
    }

    private function enforceSingleLocaleConfiguration(): void
    {
        $locale = $this->singleLocale();
        $configuredLocales = config('app.locales', []);
        $localeConfig = null;

        if (is_array($configuredLocales) && isset($configuredLocales[$locale]) && is_array($configuredLocales[$locale])) {
            $localeConfig = $configuredLocales[$locale];
        }

        if (! is_array($localeConfig)) {
            $localeConfig = [
                'name'      => strtoupper($locale),
                'native'    => strtoupper($locale),
                'direction' => 'ltr',
            ];
        }

        config()->set('app.locale', $locale);
        config()->set('app.fallback_locale', $locale);
        config()->set('app.supported_locales', [$locale]);
        config()->set('app.locales', [$locale => $localeConfig]);

        if (is_array(config('shared.localization.supported_locales'))) {
            config()->set('shared.localization.supported_locales', [$locale]);
        }
    }

    private function stripRouteLocale(Request $request): string
    {
        $path = '/' . ltrim((string) $request->getPathInfo(), '/');
        $segments = explode('/', ltrim($path, '/'));

        if ($segments === [] || $segments[0] === '') {
            return url('/');
        }

        array_shift($segments);
        $normalizedPath = trim(implode('/', $segments), '/');

        $queryString = (string) $request->getQueryString();

        return url('/' . $normalizedPath) . ($queryString !== '' ? ('?' . $queryString) : '');
    }
}
