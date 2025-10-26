<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Services\Images\ProductImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProductRandomImagesSeeder extends Seeder
{
    private ProductImageService $imageService;

    public function __construct()
    {
        $this->imageService = app(ProductImageService::class);
    }

    public function run(): void
    {
        $this->command->info('🎨 Pradedame generuoti atsitiktinius produktų paveikslėlius...');

        // Get existing products or create some if none exist
        $products = Product::query()
            ->with('media')
            ->whereDoesntHave('media', function ($q) {
                $q->where('collection_name', 'images');
            })
            ->get();

        if ($products->isEmpty()) {
            $this->command->info('Nėra produktų be paveikslėlių. Kuriame naujus produktus...');
            $products = Product::factory()
                ->count(20)
                ->create();
        }

        foreach ($products as $product) {
            $this->generateImagesForProduct($product);
        }

        $this->command->info('✅ Produktų paveikslėlių generavimas baigtas!');
    }

    private function generateImagesForProduct(Product $product): void
    {
        try {
            $this->command->info("🖼️ Generuojame paveikslėlius produktui: {$product->name}");

            // Generate 2-4 random images per product
            $imageCount = random_int(2, 4);

            for ($i = 0; $i < $imageCount; $i++) {
                $imagePath = $this->imageService->generateProductImage($product);

                $media = $product
                    ->addMedia($imagePath)
                    ->withCustomProperties([
                        'generated'    => true,
                        'product_name' => $product->name,
                        'image_number' => $i + 1,
                        'alt_text'     => __('translations.product_image_alt', ['name' => $product->name, 'number' => $i + 1]),
                    ])
                    ->usingName($product->name . ' - ' . __('translations.image') . ' ' . ($i + 1))
                    ->usingFileName('product_' . $product->id . '_image_' . ($i + 1) . '.webp')
                    ->toMediaCollection('images');

                // Clean up temporary file
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $this->command->info('   ✓ Paveikslėlis #' . ($i + 1) . " sukurtas: {$media->name}");
            }
        } catch (Throwable $e) {
            Log::warning('Nepavyko sugeneruoti paveikslėlio produktui', [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'error'        => $e->getMessage(),
            ]);

            $this->command->error("   ❌ Klaida generuojant paveikslėlius produktui {$product->name}: {$e->getMessage()}");
        }
    }
}
