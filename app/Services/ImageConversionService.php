<?php declare(strict_types=1);

namespace App\Services;

use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Conversions\Conversion;

final class ImageConversionService
{
    /**
     * Generate WebP conversions for brand images
     */
    public static function addBrandLogoConversions(Conversion $conversion): Conversion
    {
        return $conversion
            ->performOnCollections('logo')
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();
    }

    /**
     * Generate WebP conversions for brand banners
     */
    public static function addBrandBannerConversions(Conversion $conversion): Conversion
    {
        return $conversion
            ->performOnCollections('banner')
            ->format('webp')
            ->quality(85)
            ->sharpen(5)
            ->optimize();
    }

    /**
     * Generate responsive WebP images manually using Spatie Image
     */
    public static function generateWebPVariants(Media $media, string $collection): void
    {
        $originalPath = $media->getPath();
        $basePath = dirname($originalPath);
        $filename = pathinfo($media->file_name, PATHINFO_FILENAME);

        $sizes = match($collection) {
            'logo' => [
                'xs' => ['width' => 64, 'height' => 64],
                'sm' => ['width' => 128, 'height' => 128],
                'md' => ['width' => 200, 'height' => 200],
                'lg' => ['width' => 400, 'height' => 400],
            ],
            'banner' => [
                'sm' => ['width' => 800, 'height' => 400],
                'md' => ['width' => 1200, 'height' => 600],
                'lg' => ['width' => 1920, 'height' => 960],
            ],
            default => [],
        };

        foreach ($sizes as $size => $dimensions) {
            $outputPath = $basePath . "/conversions/{$filename}-{$collection}-{$size}.webp";
            
            try {
                Image::load($originalPath)
                    ->fit(Fit::Contain, $dimensions['width'], $dimensions['height'])
                    ->quality(85)
                    ->save($outputPath);
            } catch (\Exception $e) {
                logger()->warning("Failed to generate WebP conversion for {$media->name}: " . $e->getMessage());
            }
        }
    }
}
