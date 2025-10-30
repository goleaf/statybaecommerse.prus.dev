<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Throwable;

final class ProductImageSeeder extends Seeder
{
    private const FALLBACK_PIXEL = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO2aXhQAAAAASUVORK5CYII=';

    public function __construct(private readonly LocalImageGeneratorService $imageGenerator)
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Product::query()->exists()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        Product::query()
            ->orderBy('id')
            ->chunkById(50, function (Collection $products): void {
                // Process each chunk to keep memory usage predictable while ensuring every product receives images.
                $this->seedImagesForProducts($products);
            });
    }

    /**
     * Create main product images
     */
    private function seedImagesForProducts(Collection $products): void
    {
        // Iterate each product inside the chunk so we can attach individual image sets and derived variations per product.
        foreach ($products as $product) {
            $this->createProductImages($product);
            $this->createImageVariations($product);
        }
    }

    /**
     * Create main product images
     */
    private function createProductImages(Product $product): void
    {
        $imageCount = rand(3, 8);  // Each product gets 3-8 images
        $createdImages = 0;

        // Main product image
        $this->createImageForProduct($product, 'main', 'Main product image', 1);
        $createdImages++;

        // Gallery images
        $galleryCount = min(rand(2, 4), $imageCount - $createdImages);
        for ($i = 0; $i < $galleryCount; $i++) {
            $this->createImageForProduct(
                $product,
                'gallery',
                'Gallery image ' . ($i + 1),
                $i + 2
            );
            $createdImages++;
        }

        // Lifestyle images (if space allows)
        if ($createdImages < $imageCount) {
            $lifestyleCount = min(rand(1, 2), $imageCount - $createdImages);
            for ($i = 0; $i < $lifestyleCount; $i++) {
                $this->createImageForProduct(
                    $product,
                    'lifestyle',
                    'Lifestyle image ' . ($i + 1),
                    $createdImages + $i + 1
                );
                $createdImages++;
            }
        }

        // Technical images (if space allows)
        if ($createdImages < $imageCount) {
            $technicalCount = min(rand(1, 2), $imageCount - $createdImages);
            for ($i = 0; $i < $technicalCount; $i++) {
                $this->createImageForProduct(
                    $product,
                    'technical',
                    'Technical image ' . ($i + 1),
                    $createdImages + $i + 1
                );
                $createdImages++;
            }
        }
    }

    /**
     * Create image variations for products
     */
    private function createImageVariations(Product $product): void
    {
        // Create different size variations for main images so responsive breakpoints have assets to render.
        $sizes = ['thumb', 'small', 'medium', 'large', 'xlarge'];

        foreach ($sizes as $size) {
            $path = "product-images/{$product->id}/{$size}-image.jpg";

            // Create a physical placeholder for this variation so URLs resolve
            $dimensions = match ($size) {
                'thumb'  => [200, 200],
                'small'  => [400, 400],
                'medium' => [600, 600],
                'large'  => [800, 800],
                'xlarge' => [1200, 1200],
                default  => [600, 600],
            };
            $this->createPlaceholderImage(
                path: $path,
                width: $dimensions[0],
                height: $dimensions[1],
                label: sprintf('%s %s', $product->name, ucfirst($size)),
            );

            if (ProductImage::query()->where('product_id', $product->id)->where('path', $path)->doesntExist()) {
                ProductImage::factory()
                    ->for($product)
                    ->create([
                        'path'       => $path,
                        'alt_text'   => "{$product->name} - {$size} image",
                        'sort_order' => $this->getSortOrderForSize($size),
                    ]);
            }
        }
    }

    /**
     * Create a single image for a product using factory
     */
    private function createImageForProduct(Product $product, string $type, string $altText, int $sortOrder): void
    {
        $imagePath = $this->generateImagePath($product, $type, $sortOrder);

        // Ensure a physical placeholder exists for the generated path
        $this->createPlaceholderImage(
            path: $imagePath,
            width: 800,
            height: 800,
            label: sprintf('%s %s', $product->name, ucfirst($type)),
        );

        // Skip creating duplicate entries when the seeder is executed multiple times for smoke testing.
        if (ProductImage::query()->where('product_id', $product->id)->where('path', $imagePath)->exists()) {
            return;
        }

        ProductImage::factory()
            ->for($product)
            ->create([
                'path'       => $imagePath,
                'alt_text'   => $altText,
                'sort_order' => $sortOrder,
            ]);
    }

    /**
     * Generate image path for a product
     */
    private function generateImagePath(Product $product, string $type, int $sortOrder): string
    {
        $productSlug = strtolower(str_replace(' ', '-', $product->name));
        $productSlug = preg_replace('/[^a-z0-9\-]/', '', $productSlug);

        return "product-images/{$productSlug}/{$type}-{$sortOrder}.jpg";
    }

    /**
     * Get sort order for different image sizes
     */
    private function getSortOrderForSize(string $size): int
    {
        return match ($size) {
            'thumb'  => 1,
            'small'  => 2,
            'medium' => 3,
            'large'  => 4,
            'xlarge' => 5,
            default  => 0,
        };
    }

    /**
     * Create placeholder images in storage
     */
    private function createPlaceholderImages(): void
    {
        $products = Product::limit(10)->get();

        foreach ($products as $product) {
            $productSlug = strtolower(str_replace(' ', '-', $product->name));
            $productSlug = preg_replace('/[^a-z0-9\-]/', '', $productSlug);

            // Create directory structure
            $basePath = "product-images/{$productSlug}";

            // Create different sized placeholder images
            $sizes = [
                'thumb'  => [200, 200],
                'small'  => [400, 400],
                'medium' => [600, 600],
                'large'  => [800, 800],
                'xlarge' => [1200, 1200],
            ];

            foreach ($sizes as $sizeName => $dimensions) {
                $imagePath = "{$basePath}/{$sizeName}-image.jpg";

                // Create a placeholder for each responsive size using the image generator service.
                $this->createPlaceholderImage(
                    path: $imagePath,
                    width: $dimensions[0],
                    height: $dimensions[1],
                    label: sprintf('%s %s', $product->name, ucfirst($sizeName)),
                );
            }
        }
    }

    /**
     * Create a placeholder image file
     */
    private function createPlaceholderImage(string $path, int $width, int $height, string $label): void
    {
        // Produce descriptive text so generated placeholders remain meaningful in demos.
        $label = trim($label) !== '' ? $label : sprintf('%dx%d', $width, $height);

        $fullPath = storage_path("app/public/{$path}");

        if (file_exists($fullPath)) {
            return;
        }

        try {
            $this->imageGenerator->generatePlaceholderImageFile(
                text: $label,
                width: $width,
                height: $height,
                targetPath: $fullPath,
            );
        } catch (Throwable $exception) {
            // Defer to a 1px placeholder so broken image icons are avoided in constrained environments.
            $this->writePixelFallback($fullPath);

            return;
        }

        if (! file_exists($fullPath)) {
            $this->writePixelFallback($fullPath);
        }
    }

    /**
     * Persist a deterministic pixel fallback when dynamic generation fails.
     */
    private function writePixelFallback(string $fullPath): void
    {
        $pixel = base64_decode(self::FALLBACK_PIXEL);
        @file_put_contents($fullPath, $pixel !== false ? $pixel : '');
    }
}
