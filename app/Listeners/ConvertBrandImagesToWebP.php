<?php declare(strict_types=1);

namespace App\Listeners;

use App\Models\Brand;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

final class ConvertBrandImagesToWebP
{
    /**
     * Handle the event.
     */
    public function handle(MediaHasBeenAdded $event): void
    {
        $media = $event->media;

        // Only process Brand model images
        if (!$media->model instanceof Brand) {
            return;
        }

        // Only process logo and banner collections
        if (!in_array($media->collection_name, ['logo', 'banner'])) {
            return;
        }

        $this->generateWebPConversions($media);
    }

    /**
     * Generate WebP conversions for the uploaded image
     */
    private function generateWebPConversions($media): void
    {
        $originalPath = $media->getPath();
        $conversionsPath = dirname($originalPath) . '/conversions';

        // Ensure conversions directory exists
        if (!is_dir($conversionsPath)) {
            mkdir($conversionsPath, 0755, true);
        }

        $filename = pathinfo($media->file_name, PATHINFO_FILENAME);
        $collection = $media->collection_name;

        $sizes = match ($collection) {
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
            $webpPath = "{$conversionsPath}/{$filename}-{$collection}-{$size}.webp";

            try {
                Image::load($originalPath)
                    ->fit(Fit::Contain, $dimensions['width'], $dimensions['height'])
                    ->quality(85)
                    ->save($webpPath);

                logger()->info("Generated WebP conversion: {$webpPath}");
            } catch (\Exception $e) {
                logger()->warning("Failed to generate WebP conversion for {$media->name}: " . $e->getMessage());
            }
        }
    }
}
