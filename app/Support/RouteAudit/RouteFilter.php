<?php

declare(strict_types=1);

namespace App\Support\RouteAudit;

use Illuminate\Routing\Route;

final class RouteFilter
{
    /**
     * Determine if a route should be excluded from dynamic probing.
     */
    public function shouldIgnore(Route $route): bool
    {
        $uri = ltrim($route->uri(), '/');
        $name = (string) $route->getName();

        foreach ($this->ignoredNamePatterns() as $pattern) {
            if ($name !== '' && preg_match($pattern, $name) === 1) {
                return true;
            }
        }

        foreach ($this->ignoredUriPatterns() as $pattern) {
            if (preg_match($pattern, $uri) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function ignoredUriPatterns(): array
    {
        return [
            '#^_ignition#',
            '#^telescope#',
            '#^horizon#',
            '#^debugbar#',
            '#^sanctum/csrf-cookie$#',
            '#^livewire/message#',
            '#^livewire/upload-file$#',
            '#^vite#',
            '#^up$#',
            '#^health#',
        ];
    }

    /**
     * @return list<string>
     */
    private function ignoredNamePatterns(): array
    {
        return [
            '#^ignition#',
            '#^telescope#',
            '#^horizon#',
            '#^debugbar#',
            '#^sanctum\.csrf-cookie$#',
            '#^livewire#',
            '#^vite#',
            '#^health#',
        ];
    }
}
