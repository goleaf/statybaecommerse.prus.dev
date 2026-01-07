<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\VersionCompatibility\FileProcessor;
use App\Services\VersionCompatibility\Security\FileSecurityValidator;
use App\Services\VersionCompatibility\Security\RateLimiter;
use App\Services\VersionCompatibilityService;
use Illuminate\Cache\RateLimiter as LaravelRateLimiter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for version compatibility services with enhanced security
 */
final class VersionCompatibilityServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register FileProcessor
        $this->app->singleton(FileProcessor::class, function ($app) {
            return new FileProcessor(
                $app->make(Filesystem::class)
            );
        });

        // Register security validator
        $this->app->singleton(FileSecurityValidator::class, function ($app) {
            return new FileSecurityValidator(
                $app->make(Filesystem::class),
                $app->make(ConfigRepository::class)
            );
        });

        // Register rate limiter
        $this->app->singleton(RateLimiter::class, function ($app) {
            $config = $app->make(ConfigRepository::class);
            $rateLimitConfig = $config->get('version-compatibility.security.rate_limiting', []);

            return new RateLimiter(
                $app->make(LaravelRateLimiter::class),
                $rateLimitConfig
            );
        });

        // Register main service with security dependencies
        $this->app->singleton(VersionCompatibilityService::class, function ($app) {
            return new VersionCompatibilityService(
                $app->make(FileProcessor::class),
                $app->make(Filesystem::class),
                $app->make(CacheRepository::class),
                $app->make(ConfigRepository::class),
                $app->make(FileSecurityValidator::class),
                $app->make(RateLimiter::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/version-compatibility.php' => config_path('version-compatibility.php'),
        ], 'version-compatibility-config');
    }
}
