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
        $locale = 'lt';

        App::setLocale($locale);
        Session::put('locale', $locale);
        Session::put('admin_locale', $locale);
        Session::put('app.locale', $locale);

        return $next($request);
    }
}
