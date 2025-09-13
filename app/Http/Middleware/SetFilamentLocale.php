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
        // Get locale from request parameter, session, or default to Lithuanian
        $locale = $request->get('locale')
            ?? Session::get('locale')
            ?? config('app.locale', 'lt');

        // Validate against configured locales
        $supported = config('app.supported_locales', ['lt', 'en']);
        $supportedLocales = is_array($supported)
            ? $supported
            : array_filter(array_map('trim', explode(',', (string) $supported)));
        $supportedLocales = array_map('trim', $supportedLocales);
        if (! in_array($locale, $supportedLocales, true)) {
            $locale = (string) (config('app.locale', 'lt'));
        }

        // Set the locale
        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
