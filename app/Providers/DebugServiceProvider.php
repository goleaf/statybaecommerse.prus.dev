<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Debug\DiscountDebugCollector;
use App\Services\Debug\EcommerceDebugCollector;
use App\Services\Debug\LivewireDebugCollector;
use App\Services\Debug\NPlusOneDetector as QueryInspector;
use App\Services\Debug\TranslationDebugCollector;
use App\Support\Debug\NPlusOneDetector as AggregatedInspector;
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
            $this->app->singleton(AggregatedInspector::class, function () {
                $detector = new AggregatedInspector;
                $detector->boot();

                return $detector;
            });
        }

        if ($this->app->environment('local')) {
            $this->app->singleton(QueryInspector::class, static function (): QueryInspector {
                $detector = new QueryInspector;
                $detector->register();

                return $detector;
            });
        }
    }

    public function boot(): void
    {
        if ($this->app->environment('local', 'staging')) {
            $this->app->make(AggregatedInspector::class);
        }

        if ($this->app->environment('local')) {
            $this->app->make(QueryInspector::class);
        }
    }
}
