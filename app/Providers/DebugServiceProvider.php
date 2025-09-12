<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Debug\DiscountDebugCollector;
use App\Services\Debug\EcommerceDebugCollector;
use App\Services\Debug\LivewireDebugCollector;
use App\Services\Debug\TranslationDebugCollector;
use Illuminate\Support\ServiceProvider;

class DebugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local', 'staging')) {
            $this->app->singleton('debugbar.discount', fn () => new DiscountDebugCollector);
            $this->app->singleton('debugbar.translation', fn () => new TranslationDebugCollector);
            $this->app->singleton('debugbar.livewire', fn () => new LivewireDebugCollector);
            $this->app->singleton('debugbar.ecommerce', fn () => new EcommerceDebugCollector);
        }
    }

    public function boot(): void
    {
        // No boot actions required currently
    }
}
