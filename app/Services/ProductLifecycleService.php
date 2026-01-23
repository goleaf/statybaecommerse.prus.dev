<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Services\Images\GradientImageService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Coordinates domain side effects for product lifecycle events triggered by observers.
 */
final class ProductLifecycleService
{
    public function __construct(
        private readonly CacheInvalidationService $cacheInvalidationService,
        private readonly GradientImageService $gradientImageService,
    ) {}

    /**
     * Handle a freshly created product by flushing caches and generating placeholder imagery when needed.
     */
    public function handleCreated(Product $product): void
    {
        // Immediately clear any cached catalogue payloads so downstream services work with fresh data.
        $this->cacheInvalidationService->flushProducts($product);

        // Keep test suites lightweight by skipping placeholder generation while still invalidating caches.
        if (app()->environment('testing')) {
            return;
        }

        try {
            $collection = 'gallery'; // Spatie media collection used for main product images.

            if ($product->getMedia($collection)->isNotEmpty()) {
                // A media item already exists; refresh the aggregate caches and exit early.
                return;
            }

            // Generate a deterministic gradient placeholder and attach it to the product media collection.
            $temporaryPath = $this->gradientImageService->generateGradientPng(800, 800);

            $product
                ->addMedia($temporaryPath)
                ->withCustomProperties(['placeholder' => true])
                ->preservingOriginal()
                ->toMediaCollection($collection);
        } catch (Throwable $exception) {
            // Never block product creation on placeholder issues; emit a warning for later diagnostics.
            Log::warning('Failed to attach placeholder image for product', [
                'product_id' => $product->id,
                'error'      => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Handle product mutations (update/delete/restore) by delegating cache invalidation to the dedicated service.
     */
    public function handleMutated(Product $product): void
    {
        // Use the shared cache invalidation service so storefront and dashboard widgets stay in sync.
        $this->cacheInvalidationService->flushProducts($product);
    }
}
