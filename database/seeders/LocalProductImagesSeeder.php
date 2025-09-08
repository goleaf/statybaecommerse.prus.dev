<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

final class LocalProductImagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Adding local product images...');
        
        // Create simple colored placeholder images for different product types
        $this->createProductImages();
        
        // Assign images to products
        $this->assignImagesToProducts();
        
        $this->command->info('Local product images seeding completed!');
    }

    private function createProductImages(): void
    {
        $imageDir = public_path('images/products');
        
        if (!File::exists($imageDir)) {
            File::makeDirectory($imageDir, 0755, true);
        }

        // Create simple SVG images for different product categories
        $productImages = [
            'drill.svg' => $this->createDrillSvg(),
            'hammer.svg' => $this->createHammerSvg(),
            'saw.svg' => $this->createSawSvg(),
            'screwdriver.svg' => $this->createScrewdriverSvg(),
            'wrench.svg' => $this->createWrenchSvg(),
            'level.svg' => $this->createLevelSvg(),
            'safety-helmet.svg' => $this->createHelmetSvg(),
            'safety-boots.svg' => $this->createBootsSvg(),
            'measuring-tape.svg' => $this->createMeasuringTapeSvg(),
            'pliers.svg' => $this->createPliersSvg(),
        ];

        foreach ($productImages as $filename => $content) {
            $path = $imageDir . '/' . $filename;
            if (!File::exists($path)) {
                File::put($path, $content);
                $this->command->info("Created: {$filename}");
            }
        }
    }

    private function assignImagesToProducts(): void
    {
        $imageFiles = [
            'drill.svg', 'hammer.svg', 'saw.svg', 'screwdriver.svg', 'wrench.svg',
            'level.svg', 'safety-helmet.svg', 'safety-boots.svg', 'measuring-tape.svg', 'pliers.svg'
        ];

        $products = Product::query()
            ->with('media')
            ->get()
            ->filter(function ($product) {
                return !$product->hasMedia('images') || 
                       $product->getMedia('images')->every(fn($media) => $media->getCustomProperty('placeholder', false));
            });

        foreach ($products as $index => $product) {
            try {
                $imageFile = $imageFiles[$index % count($imageFiles)];
                $imagePath = public_path("images/products/{$imageFile}");
                
                if (File::exists($imagePath)) {
                    // Clear existing placeholder images
                    $product->clearMediaCollection('images');
                    
                    // Add the new image
                    $product->addMedia($imagePath)
                        ->withCustomProperties(['source' => 'local_svg'])
                        ->usingName($product->name . ' Image')
                        ->toMediaCollection('images');
                    
                    $this->command->info("✓ Added {$imageFile} to: {$product->name}");
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to add local image to product', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function createDrillSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(50,150)">
                <rect x="0" y="50" width="200" height="40" fill="#2563eb" rx="5"/>
                <circle cx="220" cy="70" r="15" fill="#374151"/>
                <rect x="235" y="65" width="60" height="10" fill="#6b7280"/>
                <rect x="20" y="30" width="20" height="80" fill="#1f2937"/>
                <text x="200" y="130" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Gręžtuvas</text>
            </g>
        </svg>';
    }

    private function createHammerSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(100,100)">
                <rect x="80" y="10" width="40" height="60" fill="#374151" rx="5"/>
                <rect x="95" y="70" width="10" height="120" fill="#92400e"/>
                <text x="100" y="210" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Plaktukas</text>
            </g>
        </svg>';
    }

    private function createSawSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(50,150)">
                <rect x="0" y="40" width="150" height="80" fill="#dc2626" rx="10"/>
                <circle cx="75" cy="80" r="50" fill="#374151" stroke="#6b7280" stroke-width="2"/>
                <rect x="150" y="70" width="100" height="20" fill="#92400e"/>
                <text x="150" y="140" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Pjūklas</text>
            </g>
        </svg>';
    }

    private function createScrewdriverSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(150,80)">
                <rect x="0" y="0" width="10" height="200" fill="#dc2626"/>
                <rect x="-10" y="200" width="30" height="40" fill="#374151" rx="5"/>
                <text x="5" y="260" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Atsuktuvas</text>
            </g>
        </svg>';
    }

    private function createWrenchSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(80,150)">
                <rect x="0" y="30" width="200" height="20" fill="#6b7280"/>
                <rect x="0" y="20" width="30" height="40" fill="#374151"/>
                <rect x="170" y="20" width="30" height="40" fill="#374151"/>
                <text x="100" y="80" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Raktas</text>
            </g>
        </svg>';
    }

    private function createLevelSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(50,180)">
                <rect x="0" y="0" width="300" height="40" fill="#eab308" rx="5"/>
                <circle cx="150" cy="20" r="8" fill="#374151"/>
                <text x="150" y="60" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Gulsčiukas</text>
            </g>
        </svg>';
    }

    private function createHelmetSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(150,120)">
                <path d="M0,80 Q0,20 50,20 Q100,20 100,80 L80,80 L20,80 Z" fill="#eab308"/>
                <text x="50" y="110" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Šalmas</text>
            </g>
        </svg>';
    }

    private function createBootsSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(120,150)">
                <rect x="0" y="40" width="60" height="80" fill="#374151" rx="10"/>
                <rect x="80" y="40" width="60" height="80" fill="#374151" rx="10"/>
                <text x="70" y="140" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Batai</text>
            </g>
        </svg>';
    }

    private function createMeasuringTapeSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(150,150)">
                <circle cx="0" cy="0" r="50" fill="#eab308"/>
                <rect x="50" y="-5" width="80" height="10" fill="#fbbf24"/>
                <text x="0" y="70" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Ruletė</text>
            </g>
        </svg>';
    }

    private function createPliersSvg(): string
    {
        return '<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
            <rect width="400" height="400" fill="#f8f9fa"/>
            <g transform="translate(150,120)">
                <rect x="-30" y="0" width="15" height="120" fill="#6b7280" transform="rotate(-10)"/>
                <rect x="15" y="0" width="15" height="120" fill="#6b7280" transform="rotate(10)"/>
                <rect x="-20" y="0" width="40" height="20" fill="#374151"/>
                <text x="0" y="150" text-anchor="middle" fill="#374151" font-family="Arial" font-size="16">Replės</text>
            </g>
        </svg>';
    }
}
