<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Services\Images\GradientImageService;
use Illuminate\Support\Facades\Log;

final class ProductObserver
{
    public function created(Product $product): void
    {
        // Skip placeholder image generation during tests to prevent memory issues
        if (app()->environment('testing')) {
            return;
        }

        try {
            $collection = 'gallery'; // Default collection name for product images
            if ($product->getMedia($collection)->isNotEmpty()) {
                return;
            }

            /** @var GradientImageService $generator */
            $generator = app(GradientImageService::class);
            $tmpPath = $generator->generateGradientPng(800, 800);

            $product
                ->addMedia($tmpPath)
                ->withCustomProperties(['placeholder' => true])
                ->preservingOriginal()
                ->toMediaCollection($collection);
        } catch (\Throwable $e) {
            Log::warning('Failed to attach placeholder image for product', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
