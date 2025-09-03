<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

final class RealProductImagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting to add real construction tool images to products...');
        
        // Construction tool images from Unsplash
        $toolImages = [
            'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=800&h=800&fit=crop&crop=center', // Drill
            'https://images.unsplash.com/photo-1609205264511-df4b14b59c8e?w=800&h=800&fit=crop&crop=center', // Hammer
            'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800&h=800&fit=crop&crop=center', // Screwdriver
            'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=800&h=800&fit=crop&crop=center', // Saw
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=800&fit=crop&crop=center', // Tools set
            'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&h=800&fit=crop&crop=center', // Level tool
            'https://images.unsplash.com/photo-1615149112084-4b9e2e2c3b5e?w=800&h=800&fit=crop&crop=center', // Wrench
            'https://images.unsplash.com/photo-1597149192419-0d900574b2e8?w=800&h=800&fit=crop&crop=center', // Pliers
            'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=800&h=800&fit=crop&crop=center', // Measuring tape
            'https://images.unsplash.com/photo-1609205264511-df4b14b59c8e?w=800&h=800&fit=crop&crop=center', // Power tools
            'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=800&h=800&fit=crop&crop=center', // Electric drill
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=800&fit=crop&crop=center', // Tool kit
            'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=800&h=800&fit=crop&crop=center', // Circular saw
            'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&h=800&fit=crop&crop=center', // Spirit level
            'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800&h=800&fit=crop&crop=center', // Screwdrivers
            'https://images.unsplash.com/photo-1597149192419-0d900574b2e8?w=800&h=800&fit=crop&crop=center', // Hand tools
            'https://images.unsplash.com/photo-1615149112084-4b9e2e2c3b5e?w=800&h=800&fit=crop&crop=center', // Adjustable wrench
            'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=800&h=800&fit=crop&crop=center', // Measuring tools
            'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=800&h=800&fit=crop&crop=center', // Power drill
            'https://images.unsplash.com/photo-1609205264511-df4b14b59c8e?w=800&h=800&fit=crop&crop=center', // Hammer drill
        ];

        // Get products that need images (without images or only have placeholder images)
        $products = Product::query()
            ->with('media')
            ->get()
            ->filter(function ($product) {
                if (!$product->hasMedia('images')) {
                    return true;
                }
                
                // Check if all images are placeholders
                $allPlaceholders = $product->getMedia('images')->every(function ($media) {
                    return $media->getCustomProperty('placeholder', false);
                });
                
                return $allPlaceholders;
            });

        $this->command->info("Found {$products->count()} products that need real images.");

        foreach ($products as $index => $product) {
            $imageUrl = $toolImages[$index % count($toolImages)];
            
            try {
                $this->command->info("Adding image to product: {$product->name}");
                
                // Download image
                $response = Http::timeout(30)->get($imageUrl);
                
                if ($response->successful()) {
                    $tempPath = tempnam(sys_get_temp_dir(), 'product_image_');
                    file_put_contents($tempPath, $response->body());
                    
                    // Remove existing placeholder images
                    $product->clearMediaCollection('images');
                    
                    // Add new real image
                    $product->addMedia($tempPath)
                        ->withCustomProperties(['source' => 'unsplash'])
                        ->usingName($product->name . ' Image')
                        ->toMediaCollection('images');
                    
                    // Clean up temp file
                    unlink($tempPath);
                    
                    $this->command->info("✓ Added image for: {$product->name}");
                } else {
                    $this->command->warn("Failed to download image for: {$product->name}");
                }
                
            } catch (\Throwable $e) {
                Log::warning('Failed to add image to product', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'image_url' => $imageUrl,
                    'error' => $e->getMessage()
                ]);
                $this->command->warn("Error adding image to {$product->name}: {$e->getMessage()}");
            }
        }

        $this->command->info('Real product images seeding completed!');
    }
}
