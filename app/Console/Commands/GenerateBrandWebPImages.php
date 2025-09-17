<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Brand;
use Illuminate\Console\Command;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

final class GenerateBrandWebPImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brands:generate-webp {--force : Force regeneration of existing WebP files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate WebP conversions for all brand images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $brands = Brand::whereHas('media')->get();

        if ($brands->isEmpty()) {
            $this->info('No brands with media found.');

            return self::SUCCESS;
        }

        $this->info("Processing {$brands->count()} brands...");

        $progressBar = $this->output->createProgressBar($brands->count());
        $progressBar->start();

        foreach ($brands as $brand) {
            $this->generateWebPForBrand($brand);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('WebP generation completed!');

        return self::SUCCESS;
    }

    /**
     * Generate WebP conversions for a single brand
     */
    private function generateWebPForBrand(Brand $brand): void
    {
        // Process logo
        $logoMedia = $brand->getFirstMedia('logo');
        if ($logoMedia) {
            $this->generateWebPConversions($logoMedia, 'logo');
        }

        // Process banner
        $bannerMedia = $brand->getFirstMedia('banner');
        if ($bannerMedia) {
            $this->generateWebPConversions($bannerMedia, 'banner');
        }
    }

    /**
     * Generate WebP conversions for media
     */
    private function generateWebPConversions($media, string $collection): void
    {
        $originalPath = $media->getPath();
        $conversionsPath = dirname($originalPath).'/conversions';

        // Ensure conversions directory exists
        if (! is_dir($conversionsPath)) {
            mkdir($conversionsPath, 0755, true);
        }

        $filename = pathinfo($media->file_name, PATHINFO_FILENAME);

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

            // Skip if file exists and not forcing
            if (file_exists($webpPath) && ! $this->option('force')) {
                continue;
            }

            try {
                Image::load($originalPath)
                    ->fit(Fit::Contain, $dimensions['width'], $dimensions['height'])
                    ->quality(85)
                    ->save($webpPath);
            } catch (\Exception $e) {
                $this->warn("Failed to generate {$size} WebP for {$media->name}: ".$e->getMessage());
            }
        }
    }
}
