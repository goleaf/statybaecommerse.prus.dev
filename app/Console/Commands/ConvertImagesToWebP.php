<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ConvertImagesToWebP extends Command
{
    protected $signature = 'images:convert-webp 
                           {--collection=images : Media collection to convert}
                           {--force : Force conversion even if already WebP}
                           {--quality=90 : WebP quality (1-100)}';

    protected $description = 'Convert all product images to WebP format for optimal performance';

    public function handle(): int
    {
        $this->info('🔄 Converting all product images to WebP format...');

        if (! function_exists('imagewebp')) {
            $this->error('❌ WebP support is not available in GD extension.');

            return self::FAILURE;
        }

        $collection = $this->option('collection');
        $force = $this->option('force');
        $quality = (int) $this->option('quality');

        $query = Media::where('collection_name', $collection);

        if (! $force) {
            $query->where('mime_type', '!=', 'image/webp');
        }

        $mediaItems = $query->get();

        if ($mediaItems->isEmpty()) {
            $this->info('✅ No images need conversion.');

            return self::SUCCESS;
        }

        $this->info("Converting {$mediaItems->count()} images...");
        $progressBar = $this->output->createProgressBar($mediaItems->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($mediaItems as $media) {
            try {
                if ($this->convertMediaToWebP($media, $quality)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Throwable $e) {
                $this->error("Failed to convert {$media->name}: ".$e->getMessage());
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('✅ Conversion completed!');
        $this->info("   Success: {$successCount}");
        if ($errorCount > 0) {
            $this->warn("   Errors: {$errorCount}");
        }

        return self::SUCCESS;
    }

    private function convertMediaToWebP(Media $media, int $quality): bool
    {
        $originalPath = $media->getPath();

        if (! file_exists($originalPath)) {
            $this->warn("Original file not found: {$media->file_name}");

            return false;
        }

        // Skip if already WebP and not forcing
        if ($media->mime_type === 'image/webp' && ! $this->option('force')) {
            return true;
        }

        // Get image info
        $imageInfo = getimagesize($originalPath);
        if (! $imageInfo) {
            $this->warn("Invalid image file: {$media->file_name}");

            return false;
        }

        // Create image resource
        $image = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($originalPath),
            IMAGETYPE_PNG => imagecreatefrompng($originalPath),
            IMAGETYPE_GIF => imagecreatefromgif($originalPath),
            IMAGETYPE_WEBP => imagecreatefromwebp($originalPath),
            default => null,
        };

        if (! $image) {
            $this->warn("Cannot create image resource for: {$media->file_name}");

            return false;
        }

        // Preserve transparency for PNG
        if ($imageInfo[2] === IMAGETYPE_PNG) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        // Create WebP version
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $originalPath);

        if (! imagewebp($image, $webpPath, $quality)) {
            imagedestroy($image);
            $this->warn("Failed to save WebP for: {$media->file_name}");

            return false;
        }

        imagedestroy($image);

        // Update media record
        $media->update([
            'file_name' => basename($webpPath),
            'mime_type' => 'image/webp',
            'size' => filesize($webpPath),
        ]);

        // Remove original if different
        if ($originalPath !== $webpPath && file_exists($originalPath)) {
            unlink($originalPath);
        }

        return true;
    }
}
