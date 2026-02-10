<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\TranslationHookService;
use Illuminate\Support\ServiceProvider;

final class TranslationHookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TranslationHookService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Dynamic translation macros and hooks disabled to ensure database-only 
        // translations for dynamic content.
    }
}