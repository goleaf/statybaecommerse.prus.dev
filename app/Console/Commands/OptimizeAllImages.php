<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Images\LocalImageGeneratorService;
use App\Services\Images\WebPConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class OptimizeAllImages extends Command
{
    protected $signature = 'images:optimize-all 
                           {--regenerate : Regenerate all images from scratch}
                           {--convert-only : Only convert existing images to WebP}
                           {--quality=90 : WebP quality (1-100)}';

    protected $description = 'Optimize all images: convert to WebP, regenerate conversions, and clean up';

    private WebPConversionService $conversionService;
    private LocalImageGeneratorService $imageGenerator;

    public function __construct(
        WebPConversionService $conversionService,
        LocalImageGeneratorService $imageGenerator
    ) {
        parent::__construct();
        $this->conversionService = $conversionService;
        $this->imageGenerator = $imageGenerator;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting comprehensive image optimization...');

        // Check WebP support
        $support = $this->conversionService->getWebPSupport();
        
        if (!$support['webp_support']) {
            $this->error('❌ WebP support is not available.');
            return self::FAILURE;
        }

        $this->info('✅ WebP support confirmed');

        try {
            if ($this->option('convert-only')) {
                $this->convertExistingImages();
            } elseif ($this->option('regenerate')) {
                $this->regenerateAllImages();
            } else {
                $this->optimizeAllImages();
            }

            $this->info('✅ Image optimization completed successfully!');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error during optimization: {$e->getMessage()}");
            Log::error('Image optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    private function convertExistingImages(): void
    {
        $this->info('🔄 Converting existing images to WebP...');
        $this->conversionService->convertAllCollections();
    }

    private function regenerateAllImages(): void
    {
        $this->info('🔄 Regenerating all images from scratch...');
        
        // Clear all existing media
        $this->warn('⚠️  This will delete all existing images and regenerate them!');
        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        // Delete all media files
        Media::query()->delete();
        
        // Run seeders to regenerate images
        $this->call('db:seed', [
            '--class' => 'RealProductImagesSeeder'
        ]);
        
        $this->call('db:seed', [
            '--class' => 'CategorySeeder'
        ]);
        
        $this->call('db:seed', [
            '--class' => 'BrandSeeder'
        ]);
        
        $this->call('db:seed', [
            '--class' => 'CollectionSeeder'
        ]);
    }

    private function optimizeAllImages(): void
    {
        $this->info('🔄 Optimizing all images...');
        
        // Step 1: Convert existing images to WebP
        $this->line('Step 1: Converting to WebP format...');
        $this->conversionService->convertAllCollections();
        
        // Step 2: Regenerate all conversions
        $this->line('Step 2: Regenerating image conversions...');
        $this->call('media-library:regenerate');
        
        // Step 3: Clean up unused files
        $this->line('Step 3: Cleaning up unused files...');
        $this->call('media-library:clean');
        
        // Step 4: Show statistics
        $this->showImageStatistics();
    }

    private function showImageStatistics(): void
    {
        $this->info('📊 Image Statistics:');
        
        $totalMedia = Media::count();
        $webpMedia = Media::where('mime_type', 'image/webp')->count();
        $collections = Media::distinct('collection_name')->pluck('collection_name');
        
        $this->table([
            'Metric', 'Value'
        ], [
            ['Total Images', $totalMedia],
            ['WebP Images', $webpMedia],
            ['WebP Percentage', $totalMedia > 0 ? round(($webpMedia / $totalMedia) * 100, 2) . '%' : '0%'],
            ['Collections', $collections->implode(', ')],
        ]);

        // Show collection breakdown
        $this->info('📁 Collection Breakdown:');
        $collectionStats = [];
        
        foreach ($collections as $collection) {
            $count = Media::where('collection_name', $collection)->count();
            $webpCount = Media::where('collection_name', $collection)
                            ->where('mime_type', 'image/webp')
                            ->count();
            
            $collectionStats[] = [
                $collection,
                $count,
                $webpCount,
                $count > 0 ? round(($webpCount / $count) * 100, 2) . '%' : '0%'
            ];
        }
        
        $this->table([
            'Collection', 'Total', 'WebP', 'WebP %'
        ], $collectionStats);
    }
}
