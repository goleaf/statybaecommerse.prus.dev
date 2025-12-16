<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function __construct(
        private readonly LocaleService $localeService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Resolve and set locale using centralized service
        $locale = $this->localeService->resolveAndSetLocale($request);

        // Persist locale only if it has changed (optimization for Requirements 3.2)
        $this->localeService->persistLocale($locale, $request);

        // Apply locale-specific configuration (currency mapping, etc.)
        $this->localeService->applyLocaleConfiguration($locale);

        /** @var Response $response */
        $response = $next($request);

        if (! $response->headers->has('Content-Language')) {
            // Ensure downstream responses advertise the language we resolved for this request.
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }
}
