<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Services\Images\ProductImageService;
use App\Services\Images\WebPConversionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

final class OptimizedProductImageSeeder extends Seeder
{
    private ProductImageService $imageService;

    private WebPConversionService $webpService;

    private array $categoryColors = [
        'tools' => ['#FF6B35', '#C73E1D'],
        'hardware' => ['#004E89', '#2E86AB'],
        'safety' => ['#FFD23F', '#F18F01'],
        'electrical' => ['#7209B7', '#A23B72'],
        'plumbing' => ['#2E86AB', '#004E89'],
        'garden' => ['#43e97b', '#06FFA5'],
        'automotive' => ['#F18F01', '#FFD23F'],
        'construction' => ['#C73E1D', '#FF6B35'],
        'default' => ['#667eea', '#764ba2'],
    ];

    public function __construct()
    {
        $this->imageService = app(ProductImageService::class);
        $this->webpService = app(WebPConversionService::class);
    }

    public function run(): void
    {
        $this->command->info('🚀 Pradedame optimizuotą produktų paveikslėlių generavimą...');

        // Ensure directories exist
        $this->ensureDirectoriesExist();

        // Process products in optimized batches
        DB::transaction(function () {
            $this->processProductsInBatches();
        });

        // Clean up temporary files
        $this->cleanupTempFiles();

        $this->command->info('✅ Optimizuotas produktų paveikslėlių generavimas baigtas!');
    }

    private function processProductsInBatches(): void
    {
        $totalProducts = Product::count();
        $this->command->info("📊 Rasta {$totalProducts} produktų apdorojimui");

        $processedCount = 0;
        $batchSize = 20;  // Smaller batches for better memory management

        Product::query()
            ->with(['media', 'categories', 'brand'])
            ->whereDoesntHave('media', function ($q) {
                $q
                    ->where('collection_name', 'images')
                    ->where(function ($subQ) {
                        $subQ
                            ->whereNull('custom_properties->placeholder')
                            ->orWhere('custom_properties->placeholder', false);
                    });
            })
            ->orderBy('id')
            ->chunkById($batchSize, function (Collection $products) use (&$processedCount, $totalProducts): void {
                $this->processBatch($products);
                $processedCount += $products->count();

                $percentage = round(($processedCount / $totalProducts) * 100, 1);
                $this->command->info("📈 Apdorota: {$processedCount}/{$totalProducts} ({$percentage}%)");

                // Force garbage collection to manage memory
                if ($processedCount % 100 === 0) {
                    gc_collect_cycles();
                }
            });
    }

    private function processBatch(Collection $products): void
    {
        foreach ($products as $product) {
            try {
                $this->generateOptimizedImages($product);
            } catch (\Throwable $e) {
                Log::error('Nepavyko sugeneruoti paveikslėlių produktui', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $e->getMessage(),
                ]);

                $this->command->warn("⚠️ Klaida produktui {$product->name}: {$e->getMessage()}");
            }
        }
    }

    private function generateOptimizedImages(Product $product): void
    {
        // Clear existing placeholder images
        $this->clearPlaceholderImages($product);

        // Determine number of images (2-4 based on product characteristics)
        $imageCount = $this->calculateImageCount($product);

        $this->command->info("🖼️ Generuojame {$imageCount} paveikslėlius produktui: {$product->name}");

        for ($i = 1; $i <= $imageCount; $i++) {
            $imagePath = $this->generateSingleImage($product, $i);

            if ($imagePath && file_exists($imagePath)) {
                $this->attachOptimizedImage($product, $imagePath, $i);

                // Clean up temporary file (check if it still exists after media processing)
                if (str_contains($imagePath, sys_get_temp_dir()) && file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        $this->command->info("   ✅ Sukurta {$imageCount} paveikslėlių produktui: {$product->name}");
    }

    private function generateSingleImage(Product $product, int $imageNumber): ?string
    {
        try {
            // Use different generation strategies for variety
            return match ($imageNumber % 3) {
                0 => $this->generateGradientImage($product, $imageNumber),
                1 => $this->generateCategoryImage($product, $imageNumber),
                2 => $this->generateBrandImage($product, $imageNumber),
                default => $this->generateGradientImage($product, $imageNumber),
            };
        } catch (\Throwable $e) {
            Log::warning('Image generation failed', [
                'product_id' => $product->id,
                'image_number' => $imageNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function generateGradientImage(Product $product, int $imageNumber): string
    {
        return $this->imageService->generateProductImage($product);
    }

    private function generateCategoryImage(Product $product, int $imageNumber): string
    {
        $categoryName = strtolower($product->categories->first()?->name ?? 'default');
        $colors = $this->getCategoryColors($categoryName);

        return $this->createCustomWebPImage(
            $product->name,
            $colors[0],
            $colors[1],
            $product->id,
            $imageNumber
        );
    }

    private function generateBrandImage(Product $product, int $imageNumber): string
    {
        $brandName = $product->brand?->name ?? $product->name;
        $colors = $this->getCategoryColors('default');

        return $this->createCustomWebPImage(
            $brandName,
            $colors[0],
            $colors[1],
            $product->id,
            $imageNumber,
            true  // Include brand styling
        );
    }

    private function createCustomWebPImage(
        string $text,
        string $startColor,
        string $endColor,
        int $productId,
        int $imageNumber,
        bool $brandStyle = false
    ): string {
        $width = 800;
        $height = 800;

        // Create image
        $image = imagecreatetruecolor($width, $height);

        // Create gradient background
        $this->createGradientBackground($image, $startColor, $endColor, $width, $height);

        // Add text
        $this->addStyledText($image, $text, $width, $height, $brandStyle);

        // Add decorative elements
        if (! $brandStyle) {
            $this->addDecorativeElements($image, $width, $height);
        }

        // Save as WebP
        $filename = "product_{$productId}_img_{$imageNumber}_".uniqid().'.webp';
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

        if (! imagewebp($image, $path, 90)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to save WebP image');
        }

        imagedestroy($image);

        return $path;
    }

    private function createGradientBackground($image, string $startColor, string $endColor, int $width, int $height): void
    {
        [$sr, $sg, $sb] = $this->hexToRgb($startColor);
        [$er, $eg, $eb] = $this->hexToRgb($endColor);

        // Create diagonal gradient
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $t = ($x + $y) / ($width + $height);
                $t = min(1.0, max(0.0, $t));

                $r = (int) round($sr + ($er - $sr) * $t);
                $g = (int) round($sg + ($eg - $sg) * $t);
                $b = (int) round($sb + ($eb - $sb) * $t);

                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }

    private function addStyledText($image, string $text, int $width, int $height, bool $brandStyle): void
    {
        $white = imagecolorallocate($image, 255, 255, 255);
        $shadow = imagecolorallocate($image, 0, 0, 0);

        // Prepare text
        $displayText = $this->prepareDisplayText($text, $brandStyle);
        $lines = $this->wrapText($displayText, $width);

        // Calculate positioning
        $font = 5;  // Built-in font
        $lineHeight = imagefontheight($font) + 10;
        $totalHeight = count($lines) * $lineHeight;
        $startY = ($height - $totalHeight) / 2;

        // Draw text with shadow
        foreach ($lines as $index => $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            $x = ($width - $textWidth) / 2;
            $y = $startY + ($index * $lineHeight);

            // Shadow
            imagestring($image, $font, (int) $x + 2, (int) $y + 2, $line, $shadow);
            // Main text
            imagestring($image, $font, (int) $x, (int) $y, $line, $white);
        }
    }

    private function addDecorativeElements($image, int $width, int $height): void
    {
        $decorativeColor = imagecolorallocatealpha($image, 255, 255, 255, 100);

        // Add subtle circles
        for ($i = 0; $i < 3; $i++) {
            $x = random_int(100, $width - 100);
            $y = random_int(100, $height - 100);
            $radius = random_int(30, 80);

            imagefilledellipse($image, $x, $y, $radius, $radius, $decorativeColor);
        }
    }

    private function prepareDisplayText(string $text, bool $brandStyle): string
    {
        if ($brandStyle) {
            return strtoupper($text);
        }

        // Truncate long product names
        return strlen($text) > 30 ? substr($text, 0, 27).'...' : $text;
    }

    private function wrapText(string $text, int $maxWidth): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        $font = 5;

        foreach ($words as $word) {
            $testLine = $currentLine.($currentLine ? ' ' : '').$word;
            $textWidth = imagefontwidth($font) * strlen($testLine);

            if ($textWidth > $maxWidth * 0.8) {
                if ($currentLine) {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $lines[] = $word;
                }
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    private function attachOptimizedImage(Product $product, string $imagePath, int $imageNumber): void
    {
        $customProperties = [
            'generated' => true,
            'optimized' => true,
            'product_name' => $product->name,
            'image_number' => $imageNumber,
            'generation_date' => now()->toISOString(),
            'alt_text' => __('translations.product_image_alt', [
                'name' => $product->name,
                'number' => $imageNumber,
            ]),
        ];

        $fileName = 'product_'.$product->id.'_optimized_'.$imageNumber.'.webp';

        $product
            ->addMedia($imagePath)
            ->withCustomProperties($customProperties)
            ->usingName($product->name.' - '.__('translations.image').' '.$imageNumber)
            ->usingFileName($fileName)
            ->toMediaCollection('images');
    }

    private function clearPlaceholderImages(Product $product): void
    {
        $placeholderImages = $product->getMedia('images')->filter(function ($media) {
            return $media->getCustomProperty('placeholder', false) === true;
        });

        foreach ($placeholderImages as $media) {
            $media->delete();
        }
    }

    private function calculateImageCount(Product $product): int
    {
        $baseCount = 2;

        // More images for products with variants
        if ($product->variants()->exists()) {
            $baseCount++;
        }

        // More images for featured products
        if ($product->is_featured ?? false) {
            $baseCount++;
        }

        // Random variation
        $baseCount += random_int(0, 1);

        return min($baseCount, 4);  // Maximum 4 images
    }

    private function getCategoryColors(string $categoryName): array
    {
        foreach ($this->categoryColors as $category => $colors) {
            if (str_contains($categoryName, $category)) {
                return $colors;
            }
        }

        return $this->categoryColors['default'];
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $int = hexdec($hex);

        return [($int >> 16) & 255, ($int >> 8) & 255, $int & 255];
    }

    private function ensureDirectoriesExist(): void
    {
        $directories = [
            storage_path('app/temp'),
            public_path('images/products'),
        ];

        foreach ($directories as $directory) {
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    private function cleanupTempFiles(): void
    {
        $tempDir = sys_get_temp_dir();
        $pattern = $tempDir.'/product_*';

        foreach (glob($pattern) as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 3600) {  // 1 hour old
                unlink($file);
            }
        }
    }
}
