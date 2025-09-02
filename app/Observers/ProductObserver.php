<?php declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Services\Images\GradientImageService;
use Illuminate\Support\Facades\Log;

final class ProductObserver
{
    public function created(Product $product): void
    {
        try {
            $collection = config('shopper.media.storage.collection_name');
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
