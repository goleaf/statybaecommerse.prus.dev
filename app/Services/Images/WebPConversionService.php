<?php

declare(strict_types=1);

namespace App\Services\Images;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * WebPConversionService
 *
 * Service class containing WebPConversionService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class WebPConversionService
{
    private const WEBP_QUALITY = 90;

    private const MAX_WIDTH = 1920;

    private const MAX_HEIGHT = 1920;

    private const THUMBNAIL_WIDTH = 400;

    private const THUMBNAIL_HEIGHT = 400;

    /**
     * Handle convertExistingImages functionality with proper error handling.
     */
    public function convertExistingImages(): void
    {
        Log::info('Starting WebP conversion for existing images');
        Media::query()->where('mime_type', '!=', 'image/webp')->whereIn('mime_type', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])->chunkById(50, function ($mediaItems) {
            foreach ($mediaItems as $media) {
                $this->convertMediaToWebP($media);
            }
        });
        Log::info('WebP conversion completed');
    }

    /**
     * Handle convertMediaToWebP functionality with proper error handling.
     */
    public function convertMediaToWebP(Media $media): bool
    {
        try {
            $originalPath = $media->getPath();
            if (! file_exists($originalPath)) {
                Log::warning("Original file not found: {$originalPath}");

                return false;
            }
            // Create WebP version
            $webpPath = $this->generateWebPPath($originalPath);
            if ($this->convertImageToWebP($originalPath, $webpPath)) {
                // Update media record
                $media->update(['file_name' => basename($webpPath), 'mime_type' => 'image/webp', 'size' => filesize($webpPath)]);
                // Remove original file if different from WebP
                if ($originalPath !== $webpPath && file_exists($originalPath)) {
                    unlink($originalPath);
                }
                Log::info("Successfully converted {$media->name} to WebP");

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to convert media {$media->id} to WebP: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Handle convertImageToWebP functionality with proper error handling.
     */
    public function convertImageToWebP(string $sourcePath, string $outputPath): bool
    {
        if (! extension_loaded('gd')) {
            Log::error('GD extension is required for WebP conversion');

            return false;
        }
        if (! function_exists('imagewebp')) {
            Log::error('WebP support is not available in GD extension');

            return false;
        }
        $imageInfo = getimagesize($sourcePath);
        if (! $imageInfo) {
            Log::error("Invalid image file: {$sourcePath}");

            return false;
        }
        $mimeType = $imageInfo['mime'];
        // Create image resource based on type
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };
        if (! $image) {
            Log::error("Failed to create image resource from: {$sourcePath}");

            return false;
        }
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        // Convert to WebP
        $success = imagewebp($image, $outputPath, self::WEBP_QUALITY);
        imagedestroy($image);
        if (! $success) {
            Log::error("Failed to save WebP image: {$outputPath}");

            return false;
        }

        return true;
    }

    /**
     * Handle convertAllCollections functionality with proper error handling.
     */
    public function convertAllCollections(): void
    {
        $collections = ['images', 'logo', 'banner', 'icon', 'gallery'];
        foreach ($collections as $collection) {
            Log::info("Converting {$collection} collection to WebP");
            Media::query()->where('collection_name', $collection)->where('mime_type', '!=', 'image/webp')->whereIn('mime_type', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])->chunkById(25, function ($mediaItems) {
                foreach ($mediaItems as $media) {
                    $this->convertMediaToWebP($media);
                }
            });
        }
    }

    /**
     * Handle getWebPSupport functionality with proper error handling.
     */
    public function getWebPSupport(): array
    {
        return ['gd_extension' => extension_loaded('gd'), 'webp_function' => function_exists('imagewebp'), 'webp_support' => $this->checkWebPSupport()];
    }

    /**
     * Handle checkWebPSupport functionality with proper error handling.
     */
    private function checkWebPSupport(): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }
        // Create a small test image
        $testImage = imagecreatetruecolor(1, 1);
        $tmpPath = sys_get_temp_dir().'/webp_test_'.uniqid().'.webp';
        $result = imagewebp($testImage, $tmpPath, 85);
        imagedestroy($testImage);
        if ($result && file_exists($tmpPath)) {
            unlink($tmpPath);

            return true;
        }

        return false;
    }

    /**
     * Handle generateWebPPath functionality with proper error handling.
     */
    private function generateWebPPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
    }

    /**
     * Handle convertAndOptimizeToWebP functionality with proper error handling.
     */
    public function convertAndOptimizeToWebP(string $sourcePath, ?string $outputPath = null, bool $createThumbnail = false): array
    {
        if (! file_exists($sourcePath)) {
            throw new \InvalidArgumentException("Source file does not exist: {$sourcePath}");
        }
        $results = [];
        // Generate output path if not provided
        if (! $outputPath) {
            $pathInfo = pathinfo($sourcePath);
            $outputPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
        }
        // Convert main image
        $mainImage = $this->processImage($sourcePath, $outputPath, self::MAX_WIDTH, self::MAX_HEIGHT);
        if ($mainImage) {
            $results['main'] = $mainImage;
        }
        // Create thumbnail if requested
        if ($createThumbnail) {
            $thumbnailPath = $this->generateThumbnailPath($outputPath);
            $thumbnail = $this->processImage($sourcePath, $thumbnailPath, self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
            if ($thumbnail) {
                $results['thumbnail'] = $thumbnail;
            }
        }

        return $results;
    }

    /**
     * Handle processImage functionality with proper error handling.
     */
    private function processImage(string $sourcePath, string $outputPath, int $maxWidth, int $maxHeight): ?array
    {
        try {
            $imageInfo = getimagesize($sourcePath);
            if (! $imageInfo) {
                throw new \InvalidArgumentException("Invalid image file: {$sourcePath}");
            }
            [$originalWidth, $originalHeight, $imageType] = $imageInfo;
            // Create image resource based on type
            $image = match ($imageType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
                IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
                IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
                default => throw new \InvalidArgumentException("Unsupported image type: {$imageType}"),
            };
            if (! $image) {
                throw new \RuntimeException("Failed to create image resource from: {$sourcePath}");
            }
            // Calculate new dimensions maintaining aspect ratio
            [$newWidth, $newHeight] = $this->calculateDimensions($originalWidth, $originalHeight, $maxWidth, $maxHeight);
            // Create resized image if dimensions changed
            if ($newWidth !== $originalWidth || $newHeight !== $originalHeight) {
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                // Preserve transparency for PNG/GIF
                if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                    imagefill($resizedImage, 0, 0, $transparent);
                }
                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagedestroy($image);
                $image = $resizedImage;
            }
            // Ensure output directory exists
            $outputDir = dirname($outputPath);
            if (! is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            // Save as WebP
            $success = imagewebp($image, $outputPath, self::WEBP_QUALITY);
            imagedestroy($image);
            if (! $success) {
                throw new \RuntimeException("Failed to save WebP image: {$outputPath}");
            }

            return ['path' => $outputPath, 'size' => filesize($outputPath), 'width' => $newWidth, 'height' => $newHeight, 'original_size' => filesize($sourcePath), 'compression_ratio' => round((1 - filesize($outputPath) / filesize($sourcePath)) * 100, 2)];
        } catch (\Throwable $e) {
            Log::error('Image processing failed', ['source' => $sourcePath, 'output' => $outputPath, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Handle calculateDimensions functionality with proper error handling.
     */
    private function calculateDimensions(int $originalWidth, int $originalHeight, int $maxWidth, int $maxHeight): array
    {
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return [$originalWidth, $originalHeight];
        }
        $widthRatio = $maxWidth / $originalWidth;
        $heightRatio = $maxHeight / $originalHeight;
        $ratio = min($widthRatio, $heightRatio);

        return [(int) round($originalWidth * $ratio), (int) round($originalHeight * $ratio)];
    }

    /**
     * Handle generateThumbnailPath functionality with proper error handling.
     */
    private function generateThumbnailPath(string $mainPath): string
    {
        $pathInfo = pathinfo($mainPath);

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'_thumb.webp';
    }

    /**
     * Handle batchConvertImages functionality with proper error handling.
     */
    public function batchConvertImages(array $imagePaths, ?callable $progressCallback = null): array
    {
        $results = [];
        $total = count($imagePaths);
        foreach ($imagePaths as $index => $imagePath) {
            try {
                $result = $this->convertAndOptimizeToWebP($imagePath, null, true);
                $results[$imagePath] = $result;
                if ($progressCallback) {
                    $progressCallback($index + 1, $total, $imagePath, $result);
                }
            } catch (\Throwable $e) {
                $results[$imagePath] = ['error' => $e->getMessage()];
                if ($progressCallback) {
                    $progressCallback($index + 1, $total, $imagePath, ['error' => $e->getMessage()]);
                }
            }
        }

        return $results;
    }
}
