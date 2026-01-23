<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = explode(',', config('app.supported_locales', 'lt,en'));

        // Check if this is an admin panel request
        $isAdminPanel = $request->is('admin') || $request->is('admin/*');

        if ($isAdminPanel) {
            // For admin panel, use English as default but allow switching
            $locale = Session::get('admin_locale', 'en');
            if (! in_array($locale, $supportedLocales)) {
                $locale = 'en';
            }
        } else {
            // For frontend, use Lithuanian as default
            $locale = Session::get('locale', config('app.locale', 'lt'));
            if (! in_array($locale, $supportedLocales)) {
                $locale = config('app.locale', 'lt');
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
