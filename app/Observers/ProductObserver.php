<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheInvalidationService;
use App\Services\Images\GradientImageService;
use App\UseCases\Cache\InvalidateProductCache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ProductObserver
 *
 * Model observer for ProductObserver Eloquent model events with automatic side effect handling and data consistency.
 */
final class ProductObserver
{
    public function __construct(
        private readonly CacheInvalidationService $cacheInvalidationService,
    ) {}

    /**
     * Handle created functionality with proper error handling.
     */
    public function created(Product $product): void
    {
        $this->flushProductCaches($product);

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
        } catch (Throwable $e) {
            Log::warning('Failed to attach placeholder image for product', ['product_id' => $product->id, 'error' => $e->getMessage()]);
        }

        app(InvalidateProductCache::class)();
    }

    public function updated(Product $product): void
    {
        $this->flushProductCaches($product);
    }

    public function deleted(Product $product): void
    {
        $this->flushProductCaches($product);
    }

    public function restored(Product $product): void
    {
        $this->flushProductCaches($product);
    }

    public function forceDeleted(Product $product): void
    {
        $this->flushProductCaches($product);
    }

    /**
     * Flush cache tags tied to the provided product model.
     */
    private function flushProductCaches(Product $product): void
    {
        // Delegate to the central cache invalidation orchestrator so both taggable
        // stores and array/file fallbacks are refreshed consistently.
        $this->cacheInvalidationService->flushProducts();
    }
}
