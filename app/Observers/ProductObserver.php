<?php

declare (strict_types=1);
namespace App\Observers;

use App\Models\Product;
use App\Services\Images\GradientImageService;
use Illuminate\Support\Facades\Log;
/**
 * ProductObserver
 * 
 * Model observer for ProductObserver Eloquent model events with automatic side effect handling and data consistency.
 * 
 */
final class ProductObserver
{
    /**
     * Handle created functionality with proper error handling.
     * @param Product $product
     * @return void
     */
    public function created(Product $product): void
    {
        // Skip placeholder image generation during tests to prevent memory issues
        if (app()->environment('testing')) {
            return;
        }
        try {
            $collection = 'gallery';
            // Default collection name for product images
            if ($product->getMedia($collection)->isNotEmpty()) {
                return;
            }
            /** @var GradientImageService $generator */
            $generator = app(GradientImageService::class);
            $tmpPath = $generator->generateGradientPng(800, 800);
            $product->addMedia($tmpPath)->withCustomProperties(['placeholder' => true])->preservingOriginal()->toMediaCollection($collection);
        } catch (\Throwable $e) {
            Log::warning('Failed to attach placeholder image for product', ['product_id' => $product->id, 'error' => $e->getMessage()]);
        }
    }
}