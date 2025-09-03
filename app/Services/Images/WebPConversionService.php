<?php declare(strict_types=1);

namespace App\Services\Images;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class WebPConversionService
{
    public function convertExistingImages(): void
    {
        Log::info('Starting WebP conversion for existing images');

        Media::query()
            ->where('collection_name', 'images')
            ->where('mime_type', '!=', 'image/webp')
            ->whereIn('mime_type', ['image/jpeg', 'image/jpg', 'image/png'])
            ->chunkById(50, function ($mediaItems) {
                foreach ($mediaItems as $media) {
                    $this->convertMediaToWebP($media);
                }
            });

        Log::info('WebP conversion completed');
    }

    public function convertMediaToWebP(Media $media): bool
    {
        try {
            $originalPath = $media->getPath();
            
            if (!file_exists($originalPath)) {
                Log::warning("Original file not found for media ID: {$media->id}");
                return false;
            }

            // Check if already WebP
            if ($media->mime_type === 'image/webp') {
                return true;
            }

            $webpPath = $this->convertImageToWebP($originalPath);
            
            if ($webpPath && file_exists($webpPath)) {
                // Update media record
                $media->update([
                    'file_name' => pathinfo($webpPath, PATHINFO_BASENAME),
                    'mime_type' => 'image/webp',
                    'size' => filesize($webpPath),
                ]);

                // Replace original file
                if (rename($webpPath, $originalPath)) {
                    Log::info("Successfully converted media ID {$media->id} to WebP");
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("Failed to convert media ID {$media->id} to WebP: " . $e->getMessage());
            return false;
        }
    }

    private function convertImageToWebP(string $imagePath): ?string
    {
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('WebP support is not available in GD extension.');
        }

        $info = getimagesize($imagePath);
        if (!$info) {
            return null;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => null,
        };

        if (!$image) {
            return null;
        }

        // Preserve transparency for PNG
        if ($info[2] === IMAGETYPE_PNG) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $imagePath);
        
        if (!imagewebp($image, $webpPath, 85)) {
            imagedestroy($image);
            return null;
        }

        imagedestroy($image);
        return $webpPath;
    }

    public function getWebPSupport(): array
    {
        return [
            'gd_webp_support' => function_exists('imagewebp'),
            'imagick_webp_support' => extension_loaded('imagick') && in_array('WEBP', \Imagick::queryFormats()),
            'system_webp_support' => $this->checkSystemWebPSupport(),
        ];
    }

    private function checkSystemWebPSupport(): bool
    {
        if (function_exists('imagewebp')) {
            // Create a small test image
            $testImage = imagecreatetruecolor(1, 1);
            $tmpPath = sys_get_temp_dir() . '/webp_test_' . uniqid() . '.webp';
            
            $result = imagewebp($testImage, $tmpPath);
            imagedestroy($testImage);
            
            if ($result && file_exists($tmpPath)) {
                unlink($tmpPath);
                return true;
            }
        }
        
        return false;
    }

    public function optimizeImageQuality(string $imagePath, int $quality = 85): bool
    {
        try {
            $info = getimagesize($imagePath);
            if (!$info || $info[2] !== IMAGETYPE_WEBP) {
                return false;
            }

            $image = imagecreatefromwebp($imagePath);
            if (!$image) {
                return false;
            }

            $result = imagewebp($image, $imagePath, $quality);
            imagedestroy($image);

            return $result;
        } catch (\Throwable $e) {
            Log::error("Failed to optimize WebP quality: " . $e->getMessage());
            return false;
        }
    }
}
