<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::limit(20)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        // Create images for different product types
        $this->createProductImages($products);
        $this->createImageVariations($products);
    }

    /**
     * Create main product images
     */
    private function createProductImages($products): void
    {
        $imageTypes = [
            'main' => ['main', 'primary', 'hero'],
            'gallery' => ['gallery', 'detail', 'close-up', 'angle'],
            'lifestyle' => ['lifestyle', 'in-use', 'context'],
            'technical' => ['technical', 'specification', 'diagram'],
        ];

        foreach ($products as $product) {
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
    }

    /**
     * Create image variations for products
     */
    private function createImageVariations($products): void
    {
        foreach ($products->take(5) as $product) {
            // Create different size variations for main images
            $sizes = ['thumb', 'small', 'medium', 'large', 'xlarge'];

            foreach ($sizes as $size) {
                ProductImage::factory()
                    ->for($product)
                    ->create([
                        'path' => "product-images/{$product->id}/{$size}-image.jpg",
                        'alt_text' => "{$product->name} - {$size} image",
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

        ProductImage::factory()
            ->for($product)
            ->create([
                'path' => $imagePath,
                'alt_text' => $altText,
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
            'thumb' => 1,
            'small' => 2,
            'medium' => 3,
            'large' => 4,
            'xlarge' => 5,
            default => 0,
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
                'thumb' => [200, 200],
                'small' => [400, 400],
                'medium' => [600, 600],
                'large' => [800, 800],
                'xlarge' => [1200, 1200],
            ];

            foreach ($sizes as $sizeName => $dimensions) {
                $imagePath = "{$basePath}/{$sizeName}-image.jpg";

                // Create a simple placeholder image
                $this->createPlaceholderImage($imagePath, $dimensions[0], $dimensions[1]);
            }
        }
    }

    /**
     * Create a placeholder image file
     */
    private function createPlaceholderImage(string $path, int $width, int $height): void
    {
        // Create a simple colored rectangle as placeholder
        $image = imagecreate($width, $height);
        $backgroundColor = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
        $textColor = imagecolorallocate($image, 255, 255, 255);

        // Add text to the image
        $text = "{$width}x{$height}";
        $fontSize = min($width, $height) / 10;
        imagestring($image, 5, $width / 2 - strlen($text) * 5, $height / 2 - 10, $text, $textColor);

        // Save the image
        $fullPath = storage_path("app/public/{$path}");
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagejpeg($image, $fullPath, 80);
        imagedestroy($image);
    }
}
