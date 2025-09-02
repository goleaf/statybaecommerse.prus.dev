<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supportedConfig = config('app.supported_locales', 'en');
        $supported = collect(is_array($supportedConfig) ? $supportedConfig : explode(',', (string) $supportedConfig))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values()
            ->all();

        // Detection order: URL param > session > user preferred > cookie > Accept-Language > app default
        $param = $request->route('locale');
        $sessionLocale = session('app.locale');
        $userLocale = optional($request->user())->preferred_locale ?? null;
        $cookieLocale = $request->cookie('app_locale');
        $acceptLocale = $request->getPreferredLanguage($supported ?: null);

        $candidates = array_filter([
            is_string($param) ? $param : null,
            is_string($sessionLocale) ? $sessionLocale : null,
            is_string($userLocale) ? $userLocale : null,
            is_string($cookieLocale) ? $cookieLocale : null,
            is_string($acceptLocale) ? $acceptLocale : null,
            (string) config('app.locale'),
        ], fn($v) => (string) $v !== '');

        $locale = collect($candidates)
            ->first(fn($loc) => in_array($loc, $supported, true))
                ?: (is_array($supported) && !empty($supported) ? (string) $supported[0] : (string) config('app.locale'));

        if ($locale && (empty($supported) || in_array($locale, $supported, true))) {
            app()->setLocale($locale);
            session(['app.locale' => $locale]);
            // Persist cookie for a month
            cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));

            // Persist on user profile when authenticated
            if ($request->user() && $request->user()->preferred_locale !== $locale) {
                $request->user()->forceFill(['preferred_locale' => $locale])->save();
            }

            // Optional mapping: locale -> currency/zone
            $mapping = (array) config('app.locale_mapping', []);
            if (isset($mapping[$locale])) {
                $map = $mapping[$locale];
                if (!empty($map['currency'])) {
                    session(['forced_currency' => (string) $map['currency']]);
                }
                if (!empty($map['zone'])) {
                    session(['forced_zone' => (string) $map['zone']]);
                }
            }
        }

        return $next($request);
    }
}
