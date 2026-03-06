<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final class LocaleController
{
    public function __invoke(Request $request, ?string $locale = null): RedirectResponse
    {
        $resolved = 'lt';

        app()->setLocale($resolved);
        config()->set('app.locale', $resolved);
        config()->set('app.fallback_locale', $resolved);
        config()->set('app.supported_locales', [$resolved]);

        Session::put('locale', $resolved);
        Session::put('admin_locale', $resolved);
        Session::put('app.locale', $resolved);
        cookie()->queue(cookie('app_locale', $resolved, 60 * 24 * 30));

        $user = Auth::user();
        if ($user instanceof User && $user->preferred_locale !== $resolved) {
            $user->forceFill(['preferred_locale' => $resolved])->save();
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $this->isSafeRedirect($redirectTo, $request)) {
            return redirect()->to($redirectTo);
        }

        return redirect()->back(fallback: $this->fallbackRedirect());
    }

    private function fallbackRedirect(): string
    {
        if (Route::has('home')) {
            return route('home');
        }

        return url('/');
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

        return Str::startsWith($target, '/');
    }
}
