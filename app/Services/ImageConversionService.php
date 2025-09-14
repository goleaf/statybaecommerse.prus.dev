<?php

declare (strict_types=1);
namespace App\Services;

use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
/**
 * ImageConversionService
 * 
 * Service class containing ImageConversionService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class ImageConversionService
{
    /**
     * Handle addBrandLogoConversions functionality with proper error handling.
     * @param Conversion $conversion
     * @return Conversion
     */
    public static function addBrandLogoConversions(Conversion $conversion): Conversion
    {
        return $conversion->performOnCollections('logo')->format('webp')->quality(85)->sharpen(10)->optimize();
    }
    /**
     * Handle addBrandBannerConversions functionality with proper error handling.
     * @param Conversion $conversion
     * @return Conversion
     */
    public static function addBrandBannerConversions(Conversion $conversion): Conversion
    {
        return $conversion->performOnCollections('banner')->format('webp')->quality(85)->sharpen(5)->optimize();
    }
    /**
     * Handle generateWebPVariants functionality with proper error handling.
     * @param Media $media
     * @param string $collection
     * @return void
     */
    public static function generateWebPVariants(Media $media, string $collection): void
    {
        $originalPath = $media->getPath();
        $basePath = dirname($originalPath);
        $filename = pathinfo($media->file_name, PATHINFO_FILENAME);
        $sizes = match ($collection) {
            'logo' => ['xs' => ['width' => 64, 'height' => 64], 'sm' => ['width' => 128, 'height' => 128], 'md' => ['width' => 200, 'height' => 200], 'lg' => ['width' => 400, 'height' => 400]],
            'banner' => ['sm' => ['width' => 800, 'height' => 400], 'md' => ['width' => 1200, 'height' => 600], 'lg' => ['width' => 1920, 'height' => 960]],
            default => [],
        };
        foreach ($sizes as $size => $dimensions) {
            $outputPath = $basePath . "/conversions/{$filename}-{$collection}-{$size}.webp";
            try {
                Image::load($originalPath)->fit(Fit::Contain, $dimensions['width'], $dimensions['height'])->quality(85)->save($outputPath);
            } catch (\Exception $e) {
                logger()->warning("Failed to generate WebP conversion for {$media->name}: " . $e->getMessage());
            }
        }
    }
}