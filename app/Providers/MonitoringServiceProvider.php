<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Monitoring\ApplicationMetrics;
use App\Support\Monitoring\CacheMetricsStore;
use App\Support\Monitoring\QueueMetricsStore;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Database\ConnectionResolverInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

final class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheMetricsStore::class, function ($app): CacheMetricsStore {
            $cacheFactory = $app->make(CacheFactory::class);
            $storeName = (string) config('observability.metrics.cache_store', config('cache.default'));
            $repository = $cacheFactory->store($storeName);

            return new CacheMetricsStore($repository, (string) config('observability.metrics.cache_key'));
        });

        $this->app->singleton(QueueMetricsStore::class, function ($app): QueueMetricsStore {
            $cacheFactory = $app->make(CacheFactory::class);
            $storeName = (string) config('observability.metrics.cache_store', config('cache.default'));
            $repository = $cacheFactory->store($storeName);

            return new QueueMetricsStore($repository, (string) config('observability.metrics.queue_key'));
        });

        $this->app->singleton(ApplicationMetrics::class, fn($app): ApplicationMetrics => new ApplicationMetrics(
            $app->make(CacheMetricsStore::class),
            $app->make(QueueMetricsStore::class),
            $app->make(QueueManager::class),
            $app->make(ConnectionResolverInterface::class),
        ));
    }

    public function boot(Dispatcher $events, CacheMetricsStore $cacheMetrics, QueueMetricsStore $queueMetrics): void
    {
        $events->listen(CacheHit::class, static function (CacheHit $event) use ($cacheMetrics): void {
            $cacheMetrics->recordHit($event->storeName, $event->key);
        });

        $events->listen(CacheMissed::class, static function (CacheMissed $event) use ($cacheMetrics): void {
            $cacheMetrics->recordMiss($event->storeName, $event->key);
        });

        $events->listen(JobProcessed::class, static function (JobProcessed $event) use ($queueMetrics): void {
            $queueMetrics->recordProcessed($event);
        });

        $events->listen(JobFailed::class, static function (JobFailed $event) use ($queueMetrics): void {
            $queueMetrics->recordFailure($event);
        });
    }
}
