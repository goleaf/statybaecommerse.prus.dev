<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Images\ProductImageService;
use App\Services\Images\WebPConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class GenerateProductImages extends Command
{
    protected $signature = 'products:generate-images 
                           {--count=3 : Number of images to generate per product}
                           {--product= : Specific product ID to generate images for}
                           {--convert-existing : Convert existing images to WebP}
                           {--force : Force regenerate even if images exist}';

    protected $description = 'Generate random product images with WebP optimization';

    public function handle(): int
    {
        $this->info('🎨 ' . __('translations.generate_images') . '...');

        if ($this->option('convert-existing')) {
            $this->convertExistingImages();
        }

        $this->generateNewImages();

        $this->info('✅ ' . __('translations.image_generated') . '!');
        return self::SUCCESS;
    }

    private function convertExistingImages(): void
    {
        $this->info('🔄 Converting existing images to WebP...');
        
        $conversionService = app(WebPConversionService::class);
        $conversionService->convertExistingImages();
        
        $this->info('✅ WebP conversion completed!');
    }

    private function generateNewImages(): void
    {
        $imageService = app(ProductImageService::class);
        $count = (int) $this->option('count');
        $productId = $this->option('product');
        $force = $this->option('force');

        $query = Product::query()->with('media');

        if ($productId) {
            $query->where('id', $productId);
        } elseif (!$force) {
            $query->whereDoesntHave('media', function ($q) {
                $q->where('collection_name', 'images');
            });
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->warn('No products found for image generation.');
            return;
        }

        $this->info("Generating images for {$products->count()} products...");

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        DB::transaction(function () use ($products, $count, $imageService, $progressBar, &$successCount, &$errorCount, $force) {
            foreach ($products as $product) {
                try {
                    // Clear existing images if force is enabled
                    if ($force) {
                        $product->clearMediaCollection('images');
                    }

                    // Generate random number of images (2 to $count)
                    $imageCount = random_int(2, $count);
                    
                    for ($i = 0; $i < $imageCount; $i++) {
                        $imagePath = $imageService->generateProductImage($product);
                        
                        $product
                            ->addMedia($imagePath)
                            ->withCustomProperties([
                                'generated' => true,
                                'product_name' => $product->name,
                                'image_number' => $i + 1,
                                'alt_text' => __('translations.product_image_alt', ['name' => $product->name, 'number' => $i + 1]),
                                'generated_at' => now()->toISOString(),
                            ])
                            ->usingName($product->name . ' - ' . __('translations.image') . ' ' . ($i + 1))
                            ->usingFileName('product_' . $product->id . '_cmd_' . ($i + 1) . '.webp')
                            ->toMediaCollection('images');
                        
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                    
                    $successCount++;
                } catch (\Throwable $e) {
                    $errorCount++;
                    $this->error("Failed to generate images for product {$product->id}: " . $e->getMessage());
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully generated images for {$successCount} products");
        if ($errorCount > 0) {
            $this->warn("❌ Failed to generate images for {$errorCount} products");
        }
    }
}
