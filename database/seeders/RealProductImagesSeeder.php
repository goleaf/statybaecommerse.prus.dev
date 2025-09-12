<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class RealProductImagesSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService;
    }

    public function run(): void
    {
        $this->command->info('Generating local WebP product images...');

        // Get products that need images (without images or only have placeholder images)
        $products = Product::query()
            ->with('media', 'category')
            ->get()
            ->filter(function ($product) {
                if (! $product->hasMedia('images')) {
                    return true;
                }

                // Check if all images are placeholders
                $allPlaceholders = $product->getMedia('images')->every(function ($media) {
                    return $media->getCustomProperty('placeholder', false);
                });

                return $allPlaceholders;
            });

        $this->command->info("Found {$products->count()} products that need real images.");

        if ($products->isEmpty()) {
            $this->command->info('All products already have images.');

            return;
        }

        foreach ($products as $product) {
            try {
                $this->command->info("Generating image for product: {$product->name}");

                // Get category name for styling
                $categoryName = $product->category?->name ?? 'general';

                // Generate local WebP image
                $imagePath = $this->imageGenerator->generateProductImage(
                    $product->name,
                    $categoryName
                );

                if (file_exists($imagePath)) {
                    // Remove existing placeholder images
                    $product->clearMediaCollection('images');

                    // Add new local WebP image
                    $product
                        ->addMedia($imagePath)
                        ->withCustomProperties(['source' => 'local_generated'])
                        ->usingName($product->name.' Image')
                        ->toMediaCollection('images');

                    // Clean up temp file
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }

                    $this->command->info("✓ Generated WebP image for: {$product->name}");
                } else {
                    $this->command->warn("Failed to generate image for: {$product->name}");
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to generate image for product', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $e->getMessage(),
                ]);
                $this->command->warn("Error generating image for {$product->name}: {$e->getMessage()}");
            }
        }

        $this->command->info('Local WebP product images seeding completed!');
    }
}
