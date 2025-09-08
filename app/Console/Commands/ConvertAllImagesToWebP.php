<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Images\WebPConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class ConvertAllImagesToWebP extends Command
{
    protected $signature = 'images:convert-all-webp 
                           {--force : Force conversion even if already WebP}
                           {--quality=90 : WebP quality (1-100)}
                           {--collection= : Specific collection to convert}';

    protected $description = 'Convert all images in all collections to WebP format for optimal performance';

    private WebPConversionService $conversionService;

    public function __construct(WebPConversionService $conversionService)
    {
        parent::__construct();
        $this->conversionService = $conversionService;
    }

    public function handle(): int
    {
        $this->info('🔄 Converting all images to WebP format...');

        // Check WebP support
        $support = $this->conversionService->getWebPSupport();
        
        if (!$support['webp_support']) {
            $this->error('❌ WebP support is not available.');
            $this->line('GD Extension: ' . ($support['gd_extension'] ? '✅' : '❌'));
            $this->line('WebP Function: ' . ($support['webp_function'] ? '✅' : '❌'));
            return self::FAILURE;
        }

        $this->info('✅ WebP support is available');

        try {
            $collection = $this->option('collection');
            
            if ($collection) {
                $this->info("Converting {$collection} collection...");
                $this->convertSpecificCollection($collection);
            } else {
                $this->info('Converting all collections...');
                $this->conversionService->convertAllCollections();
            }

            $this->info('✅ All images have been converted to WebP format!');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error during conversion: {$e->getMessage()}");
            Log::error('WebP conversion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    private function convertSpecificCollection(string $collection): void
    {
        $collections = ['images', 'logo', 'banner', 'icon', 'gallery'];
        
        if (!in_array($collection, $collections)) {
            $this->error("Invalid collection: {$collection}");
            $this->line('Available collections: ' . implode(', ', $collections));
            return;
        }

        $this->conversionService->convertExistingImages();
    }
}
