<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class UltraFastProductImageSeeder extends Seeder
{
    private const BATCH_SIZE = 50;

    private const MAX_IMAGES_PER_PRODUCT = 3;

    private const IMAGE_WIDTH = 600;

    private const IMAGE_HEIGHT = 600;

    private const MEMORY_LIMIT_MB = 512;

    // Pre-allocated color palettes for ultra-fast access
    private array $colorPalettes;

    private array $gradientCache = [];

    private int $processedCount = 0;

    private int $totalProducts = 0;

    // Performance metrics
    private float $startTime;

    private array $batchTimes = [];

    public function __construct()
    {
        $this->initializeColorPalettes();
        $this->optimizeMemorySettings();
    }

    public function run(): void
    {
        $this->startTime = microtime(true);
        $this->command->info('🚀 Pradedame ultra greitą produktų paveikslėlių generavimą...');

        $this->ensureDirectoriesExist();
        $this->clearExistingPlaceholders();

        // Process in ultra-fast batches without transactions for maximum speed
        $this->processProductsUltraFast();

        $this->displayPerformanceMetrics();
        $this->cleanupResources();

        $this->command->info('✅ Ultra greitas produktų paveikslėlių generavimas baigtas!');
    }

    private function processProductsUltraFast(): void
    {
        $this->totalProducts = Product::whereDoesntHave('media', function ($q) {
            $q
                ->where('collection_name', 'images')
                ->where(function ($subQ) {
                    $subQ
                        ->whereNull('custom_properties->placeholder')
                        ->orWhere('custom_properties->placeholder', false);
                });
        })->count();

        $this->command->info("📊 Rasta {$this->totalProducts} produktų apdorojimui");

        if ($this->totalProducts === 0) {
            $this->command->info('🎯 Visi produktai jau turi paveikslėlius!');

            return;
        }

        // Ultra-fast chunked processing with minimal overhead and timeout protection
        $timeout = now()->addMinutes(30); // 30 minute timeout for image generation

        Product::query()
            ->select(['id', 'name', 'slug', 'is_featured'])
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
            ->cursor()
            ->takeUntilTimeout($timeout)
            ->chunk(self::BATCH_SIZE)
            ->each(function (Collection $products): void {
                $batchStart = microtime(true);
                $this->processBatchUltraFast($products);
                $batchTime = microtime(true) - $batchStart;
                $this->batchTimes[] = $batchTime;

                $this->processedCount += $products->count();
                $percentage = round(($this->processedCount / $this->totalProducts) * 100, 1);
                $avgBatchTime = array_sum($this->batchTimes) / count($this->batchTimes);
                $estimatedRemaining = ($this->totalProducts - $this->processedCount) / self::BATCH_SIZE * $avgBatchTime;

                $this->command->info(sprintf(
                    '⚡ Apdorota: %d/%d (%s%%) | Batch: %.2fs | ETA: %.1fs',
                    $this->processedCount,
                    $this->totalProducts,
                    $percentage,
                    $batchTime,
                    $estimatedRemaining
                ));

                // Ultra-aggressive memory management
                if ($this->processedCount % (self::BATCH_SIZE * 2) === 0) {
                    $this->aggressiveMemoryCleanup();
                }
            });
    }

    private function processBatchUltraFast(Collection $products): void
    {
        $mediaInserts = [];
        $tempFiles = [];

        foreach ($products as $product) {
            try {
                $imageCount = $this->calculateOptimalImageCount($product);

                for ($i = 1; $i <= $imageCount; $i++) {
                    $imagePath = $this->generateUltraFastImage($product, $i);

                    if ($imagePath && file_exists($imagePath)) {
                        // Prepare media insert data for bulk insert
                        $mediaData = $this->prepareMediaData($product, $imagePath, $i);
                        if ($mediaData) {
                            $mediaInserts[] = $mediaData;
                            $tempFiles[] = $imagePath;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Ultra-fast image generation failed', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Bulk insert all media records for maximum performance
        if (! empty($mediaInserts)) {
            $this->bulkInsertMedia($mediaInserts);
        }

        // Cleanup temp files after successful insert
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    private function generateUltraFastImage(Product $product, int $imageNumber): ?string
    {
        try {
            // Ultra-fast image generation with minimal operations
            $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
            if ($image === false) {
                return null;
            }

            // Use pre-computed gradient or solid color for maximum speed
            $this->applyUltraFastBackground($image, $imageNumber);

            // Minimal text rendering for speed
            $this->addMinimalText($image, $product->name);

            // Save directly to WebP with optimized settings
            $filename = sprintf('product_%d_%d_%s.webp', $product->id, $imageNumber, uniqid('', true));
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

            $success = function_exists('imagewebp')
                ? imagewebp($image, $path, 85)  // Balanced quality/speed
                : imagepng($image, str_replace('.webp', '.png', $path), 6);

            imagedestroy($image);

            return $success ? $path : null;
        } catch (\Throwable $e) {
            Log::warning('Ultra-fast image generation error', [
                'product_id' => $product->id,
                'image_number' => $imageNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function applyUltraFastBackground($image, int $imageNumber): void
    {
        $paletteIndex = $imageNumber % count($this->colorPalettes);
        $palette = $this->colorPalettes[$paletteIndex];

        // Use cached gradient or create simple solid fill for speed
        if (isset($this->gradientCache[$paletteIndex])) {
            // Apply cached gradient pattern (simplified)
            $color = imagecolorallocate($image, ...$palette['primary']);
            imagefill($image, 0, 0, $color);

            // Add simple accent
            $accentColor = imagecolorallocate($image, ...$palette['accent']);
            imagefilledrectangle($image, 0, 0, self::IMAGE_WIDTH, 50, $accentColor);
        } else {
            // Create and cache simple gradient
            $this->createSimpleGradient($image, $palette, $paletteIndex);
        }
    }

    private function createSimpleGradient($image, array $palette, int $paletteIndex): void
    {
        [$r1, $g1, $b1] = $palette['primary'];
        [$r2, $g2, $b2] = $palette['accent'];

        // Simplified vertical gradient for speed
        $steps = 20;  // Reduced steps for performance
        $stepHeight = self::IMAGE_HEIGHT / $steps;

        for ($i = 0; $i < $steps; $i++) {
            $ratio = $i / ($steps - 1);
            $r = (int) ($r1 + ($r2 - $r1) * $ratio);
            $g = (int) ($g1 + ($g2 - $g1) * $ratio);
            $b = (int) ($b1 + ($b2 - $b1) * $ratio);

            $color = imagecolorallocate($image, $r, $g, $b);
            $y1 = (int) ($i * $stepHeight);
            $y2 = (int) (($i + 1) * $stepHeight);

            imagefilledrectangle($image, 0, $y1, self::IMAGE_WIDTH, $y2, $color);
        }

        $this->gradientCache[$paletteIndex] = true;
    }

    private function addMinimalText($image, string $productName): void
    {
        // Ultra-fast text rendering with built-in fonts only
        $white = imagecolorallocate($image, 255, 255, 255);
        $shadow = imagecolorallocate($image, 0, 0, 0);

        $text = strtoupper(substr($productName, 0, 20));  // Limit text length
        $font = 5;  // Largest built-in font

        $textWidth = imagefontwidth($font) * strlen($text);
        $x = (self::IMAGE_WIDTH - $textWidth) / 2;
        $y = (self::IMAGE_HEIGHT - imagefontheight($font)) / 2;

        // Shadow
        imagestring($image, $font, (int) $x + 2, (int) $y + 2, $text, $shadow);
        // Main text
        imagestring($image, $font, (int) $x, (int) $y, $text, $white);
    }

    private function prepareMediaData(Product $product, string $imagePath, int $imageNumber): ?array
    {
        $fileInfo = pathinfo($imagePath);
        $fileName = sprintf('%s_%d.%s', $product->slug, $imageNumber, $fileInfo['extension']);
        $destinationPath = storage_path('app/public/products/'.$fileName);

        // Move file to final destination
        if (! File::move($imagePath, $destinationPath)) {
            return null;
        }

        return [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'collection_name' => 'images',
            'name' => $fileInfo['filename'],
            'file_name' => $fileName,
            'mime_type' => $fileInfo['extension'] === 'webp' ? 'image/webp' : 'image/png',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => filesize($destinationPath),
            'manipulations' => json_encode([]),
            'custom_properties' => json_encode(['placeholder' => false]),
            'generated_conversions' => json_encode([]),
            'responsive_images' => json_encode([]),
            'order_column' => $imageNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function bulkInsertMedia(array $mediaInserts): void
    {
        // Ultra-fast bulk insert with chunking to avoid memory issues
        $chunks = array_chunk($mediaInserts, 100);

        foreach ($chunks as $chunk) {
            DB::table('media')->insert($chunk);
        }
    }

    private function calculateOptimalImageCount(Product $product): int
    {
        // Ultra-fast calculation based on simple rules
        return match (true) {
            $product->is_featured => 3,
            strlen($product->name) > 15 => 2,
            default => 2,
        };
    }

    private function clearExistingPlaceholders(): void
    {
        $this->command->info('🧹 Šaliname senuosius placeholder paveikslėlius...');

        $deletedCount = Media::where('collection_name', 'images')
            ->where(function ($q) {
                $q
                    ->whereNotNull('custom_properties->placeholder')
                    ->where('custom_properties->placeholder', true);
            })
            ->delete();

        if ($deletedCount > 0) {
            $this->command->info("🗑️ Pašalinta {$deletedCount} placeholder paveikslėlių");
        }
    }

    private function initializeColorPalettes(): void
    {
        $this->colorPalettes = [
            ['primary' => [255, 107, 107], 'accent' => [78, 205, 196]],  // Red to Teal
            ['primary' => [168, 230, 207], 'accent' => [255, 217, 61]],  // Mint to Yellow
            ['primary' => [108, 92, 231], 'accent' => [162, 155, 254]],  // Purple to Light Purple
            ['primary' => [253, 121, 168], 'accent' => [253, 203, 110]],  // Pink to Orange
            ['primary' => [0, 184, 148], 'accent' => [0, 206, 201]],  // Green to Cyan
            ['primary' => [225, 112, 85], 'accent' => [253, 203, 110]],  // Orange to Yellow
            ['primary' => [108, 92, 231], 'accent' => [116, 185, 255]],  // Purple to Blue
            ['primary' => [253, 121, 168], 'accent' => [232, 67, 147]],  // Pink to Dark Pink
            ['primary' => [0, 206, 201], 'accent' => [85, 163, 255]],  // Cyan to Blue
            ['primary' => [253, 203, 110], 'accent' => [225, 112, 85]],  // Yellow to Orange
        ];
    }

    private function optimizeMemorySettings(): void
    {
        // Optimize PHP settings for maximum performance
        ini_set('memory_limit', self::MEMORY_LIMIT_MB.'M');
        ini_set('max_execution_time', '0');

        // Disable garbage collection during processing for speed
        gc_disable();
    }

    private function aggressiveMemoryCleanup(): void
    {
        // Clear gradient cache periodically
        if (count($this->gradientCache) > 20) {
            $this->gradientCache = [];
        }

        // Force garbage collection
        gc_collect_cycles();
    }

    private function ensureDirectoriesExist(): void
    {
        $directories = [
            storage_path('app/public/products'),
            sys_get_temp_dir(),
        ];

        foreach ($directories as $directory) {
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    private function displayPerformanceMetrics(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        $avgBatchTime = ! empty($this->batchTimes) ? array_sum($this->batchTimes) / count($this->batchTimes) : 0;
        $imagesPerSecond = $this->processedCount * self::MAX_IMAGES_PER_PRODUCT / $totalTime;

        $this->command->info('');
        $this->command->info('📈 PERFORMANCE METRICS:');
        $this->command->info('⏱️ Bendras laikas: '.number_format($totalTime, 2).'s');
        $this->command->info('🚀 Vidutinis batch laikas: '.number_format($avgBatchTime, 3).'s');
        $this->command->info('🖼️ Paveikslėlių per sekundę: '.number_format($imagesPerSecond, 1));
        $this->command->info('📊 Produktų per sekundę: '.number_format($this->processedCount / $totalTime, 1));
        $this->command->info('💾 Atmintis: '.number_format(memory_get_peak_usage(true) / 1024 / 1024, 1).'MB');
    }

    private function cleanupResources(): void
    {
        // Re-enable garbage collection
        gc_enable();
        gc_collect_cycles();

        // Clear caches
        $this->gradientCache = [];
        $this->batchTimes = [];

        // Clean up any remaining temp files
        $tempFiles = glob(sys_get_temp_dir().'/product_*');
        foreach ($tempFiles as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 300) {  // 5 minutes old
                unlink($file);
            }
        }
    }
}
