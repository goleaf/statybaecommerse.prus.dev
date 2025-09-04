<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService();
    }

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $collections = [
            [
                'name' => 'New Home Essentials',
                'slug' => 'new-home-essentials',
                'description' => 'Everything you need to get started with your new home construction project.',
                'sort_order' => 1,
                'image_url' => 'https://picsum.photos/600/600?random=201',
                'banner_url' => 'https://picsum.photos/1200/600?random=201',
            ],
            [
                'name' => 'Professional Contractor Tools',
                'slug' => 'professional-contractor-tools',
                'description' => 'High-quality tools trusted by professional contractors and builders.',
                'sort_order' => 2,
                'image_url' => 'https://picsum.photos/600/600?random=202',
                'banner_url' => 'https://picsum.photos/1200/600?random=202',
            ],
            [
                'name' => 'DIY Home Improvement',
                'slug' => 'diy-home-improvement',
                'description' => 'Tools and materials perfect for weekend DIY projects and home improvements.',
                'sort_order' => 3,
                'image_url' => 'https://picsum.photos/600/600?random=203',
                'banner_url' => 'https://picsum.photos/1200/600?random=203',
            ],
            [
                'name' => 'Outdoor & Landscaping',
                'slug' => 'outdoor-landscaping',
                'description' => 'Tools and equipment for outdoor construction and landscaping projects.',
                'sort_order' => 4,
                'image_url' => 'https://picsum.photos/600/600?random=204',
                'banner_url' => 'https://picsum.photos/1200/600?random=204',
            ],
            [
                'name' => 'Renovation Specialists',
                'slug' => 'renovation-specialists',
                'description' => 'Specialized tools and materials for home renovation and remodeling.',
                'sort_order' => 5,
                'image_url' => 'https://picsum.photos/600/600?random=205',
                'banner_url' => 'https://picsum.photos/1200/600?random=205',
            ],
            [
                'name' => 'Energy Efficient Solutions',
                'slug' => 'energy-efficient-solutions',
                'description' => 'Eco-friendly building materials and energy-saving construction solutions.',
                'sort_order' => 6,
                'image_url' => 'https://picsum.photos/600/600?random=206',
                'banner_url' => 'https://picsum.photos/1200/600?random=206',
            ],
        ];

        foreach ($collections as $collectionData) {
            $collection = Collection::firstOrCreate(
                ['slug' => $collectionData['slug']],
                [
                    'name' => $collectionData['name'],
                    'description' => $collectionData['description'],
                    'sort_order' => $collectionData['sort_order'],
                    'is_visible' => true,
                    'is_automatic' => false,
                ]
            );

            // Add main image if collection was created and doesn't have one
            if (($collection->wasRecentlyCreated || !$collection->hasMedia('images')) && isset($collectionData['image_url'])) {
                $this->downloadAndAttachImage($collection, $collectionData['image_url'], 'images', $collectionData['name'] . ' Image');
            }

            // Add banner if collection was created and doesn't have one
            if (($collection->wasRecentlyCreated || !$collection->hasMedia('banner')) && isset($collectionData['banner_url'])) {
                $this->downloadAndAttachImage($collection, $collectionData['banner_url'], 'banner', $collectionData['name'] . ' Banner');
            }
        }
    }

    /**
     * Download image from URL and attach it to the collection
     */
    private function downloadAndAttachImage(Collection $collection, string $imageUrl, string $collectionName, string $name): void
    {
        try {
            // Generate local WebP image
            $imagePath = $this->imageGenerator->generateCollectionImage($collection->name);

            if (file_exists($imagePath)) {
                $filename = Str::slug($name) . '.webp';

                // Add media to collection
                $collection
                    ->addMedia($imagePath)
                    ->withCustomProperties(['source' => 'local_generated'])
                    ->usingName($name)
                    ->usingFileName($filename)
                    ->toMediaCollection($collectionName);

                // Clean up temporary file
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $this->command->info("✓ Generated {$collectionName} WebP image for {$collection->name}");
            } else {
                $this->command->warn("✗ Failed to generate {$collectionName} image for {$collection->name}");
            }
        } catch (\Exception $e) {
            $this->command->warn("✗ Failed to generate {$collectionName} image for {$collection->name}: " . $e->getMessage());
        }
    }
}
