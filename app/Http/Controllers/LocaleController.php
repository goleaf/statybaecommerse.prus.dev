<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class LocaleController
{
    public function __construct(
        private readonly LocaleService $localeService
    ) {}

    public function __invoke(Request $request, ?string $locale = null): RedirectResponse
    {
        // Collect the requested locale from either the route wildcard or the submitted payload.
        $requestedRaw = $locale ?? $request->input('locale');
        $requested = is_string($requestedRaw) ? $requestedRaw : null;

        // Create a temporary request with the locale parameter for resolution
        $tempRequest = $request->duplicate();
        if ($requested !== null) {
            $tempRequest->route()->setParameter('locale', $requested);
        }

        // Use centralized locale service for resolution and persistence
        $resolved = $this->localeService->resolveAndSetLocale($tempRequest);
        $this->localeService->persistLocale($resolved, $tempRequest);
        $this->localeService->applyLocaleConfiguration($resolved);

        // Update user preference if authenticated
        $user = Auth::user();
        if ($user instanceof User && $user->preferred_locale !== $resolved) {
            $user->forceFill(['preferred_locale' => $resolved])->save();
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $this->isSafeRedirect($redirectTo, $request)) {
            return redirect()->to($redirectTo);
        }

        return redirect()->back(fallback: $this->fallbackRedirect($resolved));
    }

    private function fallbackRedirect(string $locale): string
    {
        if (Route::has('localized.home')) {
            return route('localized.home', ['locale' => $locale]);
        }

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
