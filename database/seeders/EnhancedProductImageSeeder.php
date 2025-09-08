<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Services\Images\LocalImageGeneratorService;
use App\Services\Images\ProductImageService;
use App\Services\Images\WebPConversionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

final class EnhancedProductImageSeeder extends Seeder
{
    private ProductImageService $productImageService;
    private LocalImageGeneratorService $localImageService;
    private WebPConversionService $webpService;

    private array $imageGenerationStrategies = [
        'gradient' => 40,  // 40% gradient images
        'category_styled' => 35,  // 35% category-specific styled images
        'svg_icons' => 15,  // 15% SVG icon-based images
        'placeholder' => 10,  // 10% simple placeholders
    ];

    public function __construct()
    {
        $this->productImageService = app(ProductImageService::class);
        $this->localImageService = app(LocalImageGeneratorService::class);
        $this->webpService = app(WebPConversionService::class);
    }

    public function run(): void
    {
        $this->command->info('🎨 Pradedame patobulintą produktų paveikslėlių generavimą...');

        // Create necessary directories
        $this->ensureDirectoriesExist();

        DB::transaction(function () {
            $this->processProductsInBatches();
        });

        $this->command->info('✅ Patobulinta produktų paveikslėlių generavimas baigtas!');
    }

    private function processProductsInBatches(): void
    {
        $totalProducts = Product::count();
        $this->command->info("📊 Rasta {$totalProducts} produktų apdorojimui");

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
            ->chunkById(25, function (Collection $products): void {
                $this->processProductBatch($products);
            });
    }

    private function processProductBatch(Collection $products): void
    {
        foreach ($products as $product) {
            try {
                $this->command->info("🖼️ Generuojame paveikslėlius produktui: {$product->name}");

                // Clear existing placeholder images
                $this->clearPlaceholderImages($product);

                // Generate multiple images with different strategies
                $this->generateProductImages($product);

                $this->command->info("   ✅ Paveikslėliai sukurti produktui: {$product->name}");
            } catch (\Throwable $e) {
                Log::error('Nepavyko sugeneruoti paveikslėlių produktui', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $this->command->error("   ❌ Klaida generuojant paveikslėlius produktui {$product->name}: {$e->getMessage()}");
            }
        }
    }

    private function generateProductImages(Product $product): void
    {
        $imageCount = $this->determineImageCount($product);
        $strategies = $this->selectImageStrategies($imageCount);

        foreach ($strategies as $index => $strategy) {
            $imagePath = $this->generateImageByStrategy($product, $strategy, $index + 1);

            if ($imagePath && file_exists($imagePath)) {
                $this->attachImageToProduct($product, $imagePath, $strategy, $index + 1);

                // Clean up temporary file (check if it still exists after media processing)
                if (str_contains($imagePath, sys_get_temp_dir()) && file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }

    private function generateImageByStrategy(Product $product, string $strategy, int $imageNumber): ?string
    {
        return match ($strategy) {
            'gradient' => $this->generateGradientImage($product, $imageNumber),
            'category_styled' => $this->generateCategoryStyledImage($product, $imageNumber),
            'svg_icons' => $this->generateSvgIconImage($product, $imageNumber),
            'placeholder' => $this->generatePlaceholderImage($product, $imageNumber),
            default => $this->generateGradientImage($product, $imageNumber),
        };
    }

    private function generateGradientImage(Product $product, int $imageNumber): ?string
    {
        try {
            return $this->productImageService->generateProductImage($product);
        } catch (\Throwable $e) {
            Log::warning('Gradient image generation failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function generateCategoryStyledImage(Product $product, int $imageNumber): ?string
    {
        try {
            $categoryName = $product->categories->first()?->name ?? 'general';
            return $this->localImageService->generateProductImage($product->name, $categoryName);
        } catch (\Throwable $e) {
            Log::warning('Category styled image generation failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function generateSvgIconImage(Product $product, int $imageNumber): ?string
    {
        try {
            $svgContent = $this->generateProductSvg($product, $imageNumber);
            $tempPath = sys_get_temp_dir() . '/product_svg_' . $product->id . '_' . $imageNumber . '.svg';

            file_put_contents($tempPath, $svgContent);

            // Convert SVG to WebP if possible
            if (class_exists('Imagick')) {
                return $this->convertSvgToWebP($tempPath, $product->id, $imageNumber);
            }

            return $tempPath;
        } catch (\Throwable $e) {
            Log::warning('SVG icon image generation failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function generatePlaceholderImage(Product $product, int $imageNumber): ?string
    {
        try {
            return $this->localImageService->generateWebPImage(
                text: $product->name,
                width: 600,
                height: 600,
                backgroundColor: $this->getProductColor($product),
                textColor: '#FFFFFF',
                filename: 'placeholder_' . $product->id . '_' . $imageNumber
            );
        } catch (\Throwable $e) {
            Log::warning('Placeholder image generation failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function generateProductSvg(Product $product, int $imageNumber): string
    {
        $categoryName = strtolower($product->categories->first()?->name ?? 'general');
        $color = $this->getProductColor($product);
        $textColor = $this->getContrastingTextColor($color);
        $categoryDisplayName = $product->categories->first()?->name ?? 'Produktas';

        $icon = $this->getCategoryIcon($categoryName);
        $adjustedColor = $this->adjustBrightness($color, -20);
        $truncatedName = $this->truncateText($product->name, 25);

        return <<<SVG
            <svg width="600" height="600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
                <defs>
                    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:{$color};stop-opacity:1" />
                        <stop offset="100%" style="stop-color:{$adjustedColor};stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect width="600" height="600" fill="url(#bg)"/>
                <g transform="translate(300,200)">
                    {$icon}
                </g>
                <text x="300" y="450" text-anchor="middle" fill="{$textColor}" font-family="Arial, sans-serif" font-size="24" font-weight="bold">
                    {$truncatedName}
                </text>
                <text x="300" y="480" text-anchor="middle" fill="{$textColor}" font-family="Arial, sans-serif" font-size="16" opacity="0.8">
                    {$categoryDisplayName}
                </text>
            </svg>
            SVG;
    }

    private function getCategoryIcon(string $categoryName): string
    {
        $icons = [
            'tools' => '<circle cx="0" cy="0" r="60" fill="white" opacity="0.2"/><rect x="-40" y="-10" width="80" height="20" fill="white" opacity="0.8" rx="10"/>',
            'hardware' => '<rect x="-50" y="-50" width="100" height="100" fill="white" opacity="0.2" rx="10"/><circle cx="0" cy="0" r="20" fill="white" opacity="0.8"/>',
            'safety' => '<polygon points="0,-60 -52,30 52,30" fill="white" opacity="0.2"/><text x="0" y="10" text-anchor="middle" fill="white" font-size="36">!</text>',
            'electrical' => '<path d="M-30,-50 L30,0 L-30,50 Z" fill="white" opacity="0.8"/>',
            'plumbing' => '<circle cx="0" cy="0" r="50" fill="white" opacity="0.2"/><rect x="-40" y="-5" width="80" height="10" fill="white" opacity="0.8"/>',
            'garden' => '<path d="M0,-50 Q-30,-20 -50,0 Q-30,20 0,50 Q30,20 50,0 Q30,-20 0,-50" fill="white" opacity="0.8"/>',
            'automotive' => '<circle cx="0" cy="0" r="50" fill="white" opacity="0.2"/><circle cx="0" cy="0" r="30" fill="white" opacity="0.8"/>',
            'construction' => '<rect x="-40" y="-40" width="80" height="80" fill="white" opacity="0.2"/><rect x="-30" y="-30" width="60" height="60" fill="white" opacity="0.8"/>',
        ];

        foreach ($icons as $category => $icon) {
            if (str_contains($categoryName, $category)) {
                return $icon;
            }
        }

        // Default icon
        return '<circle cx="0" cy="0" r="50" fill="white" opacity="0.8"/>';
    }

    private function convertSvgToWebP(string $svgPath, int $productId, int $imageNumber): string
    {
        try {
            $imagick = new \Imagick();
            $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
            $imagick->readImage($svgPath);
            $imagick->setImageFormat('webp');
            $imagick->setImageCompressionQuality(90);
            $imagick->resizeImage(600, 600, \Imagick::FILTER_LANCZOS, 1);

            $webpPath = sys_get_temp_dir() . '/product_webp_' . $productId . '_' . $imageNumber . '.webp';
            $imagick->writeImage($webpPath);
            $imagick->clear();

            // Clean up SVG file
            unlink($svgPath);

            return $webpPath;
        } catch (\Throwable $e) {
            Log::warning('SVG to WebP conversion failed', [
                'svg_path' => $svgPath,
                'error' => $e->getMessage()
            ]);
            return $svgPath;  // Return original SVG if conversion fails
        }
    }

    private function attachImageToProduct(Product $product, string $imagePath, string $strategy, int $imageNumber): void
    {
        $customProperties = [
            'generated' => true,
            'strategy' => $strategy,
            'product_name' => $product->name,
            'image_number' => $imageNumber,
            'generation_date' => now()->toISOString(),
            'alt_text' => __('translations.product_image_alt', [
                'name' => $product->name,
                'number' => $imageNumber
            ]),
        ];

        $fileName = 'product_' . $product->id . '_' . $strategy . '_' . $imageNumber;
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

        $media = $product
            ->addMedia($imagePath)
            ->withCustomProperties($customProperties)
            ->usingName($product->name . ' - ' . __('translations.image') . ' ' . $imageNumber)
            ->usingFileName($fileName . '.' . $extension)
            ->toMediaCollection('images');

        $this->command->info("   ✓ Paveikslėlis #{$imageNumber} ({$strategy}) sukurtas: {$media->name}");
    }

    private function clearPlaceholderImages(Product $product): void
    {
        $placeholderImages = $product->getMedia('images')->filter(function ($media) {
            return $media->getCustomProperty('placeholder', false) === true;
        });

        foreach ($placeholderImages as $media) {
            $media->delete();
        }

        if ($placeholderImages->isNotEmpty()) {
            $this->command->info("   🗑️ Pašalinti {$placeholderImages->count()} placeholder paveikslėliai");
        }
    }

    private function determineImageCount(Product $product): int
    {
        // Determine number of images based on product characteristics
        $baseCount = 2;

        // Add more images for products with variants
        if ($product->variants()->exists()) {
            $baseCount += 1;
        }

        // Add more images for featured products
        if ($product->is_featured ?? false) {
            $baseCount += 1;
        }

        // Random variation
        $baseCount += random_int(0, 2);

        return min($baseCount, 5);  // Maximum 5 images per product
    }

    private function selectImageStrategies(int $imageCount): array
    {
        $strategies = [];
        $availableStrategies = array_keys($this->imageGenerationStrategies);

        for ($i = 0; $i < $imageCount; $i++) {
            if ($i === 0) {
                // First image is always gradient (most reliable)
                $strategies[] = 'gradient';
            } else {
                // Select other strategies based on weights
                $strategies[] = $this->selectWeightedStrategy($availableStrategies);
            }
        }

        return $strategies;
    }

    private function selectWeightedStrategy(array $strategies): string
    {
        $totalWeight = array_sum($this->imageGenerationStrategies);
        $random = random_int(1, $totalWeight);

        $currentWeight = 0;
        foreach ($this->imageGenerationStrategies as $strategy => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $strategy;
            }
        }

        return 'gradient';  // Fallback
    }

    private function getProductColor(Product $product): string
    {
        // Generate consistent color based on product ID
        $colors = [
            '#FF6B35',
            '#004E89',
            '#FFD23F',
            '#7209B7',
            '#2E86AB',
            '#A23B72',
            '#F18F01',
            '#C73E1D',
            '#3A86FF',
            '#06FFA5',
            '#667eea',
            '#764ba2',
            '#f093fb',
            '#f5576c',
            '#4facfe',
            '#43e97b',
            '#fa709a',
            '#fee140',
            '#a8edea',
            '#d299c2',
        ];

        return $colors[$product->id % count($colors)];
    }

    private function getContrastingTextColor(string $backgroundColor): string
    {
        // Simple contrast calculation
        $hex = ltrim($backgroundColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;

        return $brightness > 128 ? '#333333' : '#FFFFFF';
    }

    private function adjustBrightness(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    private function truncateText(string $text, int $maxLength): string
    {
        return strlen($text) > $maxLength ? substr($text, 0, $maxLength - 3) . '...' : $text;
    }

    private function ensureDirectoriesExist(): void
    {
        $directories = [
            storage_path('app/temp'),
            public_path('images/products'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->command->info("📁 Sukurtas katalogas: {$directory}");
            }
        }
    }
}
