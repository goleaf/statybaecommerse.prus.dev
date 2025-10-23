<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Services\Images\GradientImageService;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ProductObserver
 *
 * Model observer for ProductObserver Eloquent model events with automatic side effect handling and data consistency.
 */
final class ProductObserver
{
    use ResolvesSupportedLocales;

    /**
     * Handle created functionality with proper error handling.
     */
    public function created(Product $product): void
    {
        $this->flushProductCaches();

        // Skip placeholder image generation during tests to prevent memory issues
        if (app()->environment('testing')) {
            app(InvalidateProductCache::class)();

            return;
        }
        try {
            $collection = 'gallery';
            // Default collection name for product images
            if ($product->getMedia($collection)->isNotEmpty()) {
                app(InvalidateProductCache::class)();

                return;
            }
            /** @var GradientImageService $generator */
            $generator = app(GradientImageService::class);
            $tmpPath = $generator->generateGradientPng(800, 800);
            $product->addMedia($tmpPath)->withCustomProperties(['placeholder' => true])->preservingOriginal()->toMediaCollection($collection);
        } catch (\Throwable $e) {
            Log::warning('Failed to attach placeholder image for product', ['product_id' => $product->id, 'error' => $e->getMessage()]);
        }

        app(InvalidateProductCache::class)();
    }

    public function updated(Product $product): void
    {
        app(InvalidateProductCache::class)();
    }

    public function deleted(Product $product): void
    {
        app(InvalidateProductCache::class)();
    }

    public function restored(Product $product): void
    {
        app(InvalidateProductCache::class)();
    }

    public function updated(Product $product): void
    {
        $this->invalidateCaches($product);
    }

    public function deleted(Product $product): void
    {
        $this->invalidateCaches($product);
    }

    private function invalidateCaches(Product $product): void
    {
        app(CacheInvalidationService::class)->flushForModel($product);
    }

    public function updated(Product $product): void
    {
        $this->flushProductCaches();
    }

    public function deleted(Product $product): void
    {
        $this->flushProductCaches();
    }

    public function restored(Product $product): void
    {
        $this->flushProductCaches();
    }

    public function forceDeleted(Product $product): void
    {
        $this->flushProductCaches();
    }

    private function flushProductCaches(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([CacheKeys::productAggregateTag()])->flush();

            return;
        }

        Cache::forget(CacheKeys::productTotalCount());

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::dashboardMetric('low_stock_items', $locale));
        }
    }
}
