<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\CountriesWithZone;
use App\Actions\ZoneSessionManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ZoneDetector
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ZoneSessionManager::checkSession()) {
            try {
                $countries = (new CountriesWithZone)->handle();

                if ($countries->isEmpty()) {
                    // No zones available, skip zone detection
                    return $next($request);
                }

                $currencyZone = $countries->firstWhere('currencyCode', app_currency());

                if ($currencyZone) {
                    ZoneSessionManager::setSession($currencyZone);
                } else {
                    $this->setDefaultZone($countries);
                }
            } catch (\Exception $e) {
                // Skip logging during tests to prevent test output issues
                if (!app()->environment('testing')) {
                    // Log the error but don't break the request
                    \Log::warning('Zone detection failed: '.$e->getMessage());
                }
                // Continue without zone detection
            }
        }

        return $next($request);
    }

    private function setDefaultZone(Collection $countries): void
    {
        $defaultZone = $countries->firstWhere('zoneCode', config('starterkit.default_zone'));

        if (! ZoneSessionManager::checkSession() && $defaultZone) {
            ZoneSessionManager::setSession($defaultZone);
        }
    }
}
