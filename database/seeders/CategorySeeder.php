<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = [
            // Main Categories
            [
                'name' => 'Power Tools',
                'slug' => 'power-tools',
                'description' => 'Professional power tools for construction and renovation projects.',
                'sort_order' => 1,
                'image_url' => 'https://picsum.photos/400/400?random=101',
                'banner_url' => 'https://picsum.photos/1200/600?random=101',
                'children' => [
                    [
                        'name' => 'Drills & Drivers',
                        'slug' => 'drills-drivers',
                        'description' => 'Cordless and corded drills, impact drivers, and screwdrivers.',
                        'sort_order' => 1,
                        'image_url' => 'https://picsum.photos/400/400?random=111',
                    ],
                    [
                        'name' => 'Saws',
                        'slug' => 'saws',
                        'description' => 'Circular saws, reciprocating saws, and jigsaws for cutting materials.',
                        'sort_order' => 2,
                        'image_url' => 'https://picsum.photos/400/400?random=112',
                    ],
                    [
                        'name' => 'Sanders & Grinders',
                        'slug' => 'sanders-grinders',
                        'description' => 'Orbital sanders, belt sanders, and angle grinders.',
                        'sort_order' => 3,
                        'image_url' => 'https://picsum.photos/400/400?random=113',
                    ],
                ],
            ],
            [
                'name' => 'Hand Tools',
                'slug' => 'hand-tools',
                'description' => 'Essential hand tools for construction and repair work.',
                'sort_order' => 2,
                'image_url' => 'https://picsum.photos/400/400?random=102',
                'banner_url' => 'https://picsum.photos/1200/600?random=102',
                'children' => [
                    [
                        'name' => 'Hammers & Mallets',
                        'slug' => 'hammers-mallets',
                        'description' => 'Claw hammers, framing hammers, and rubber mallets.',
                        'sort_order' => 1,
                        'image_url' => 'https://picsum.photos/400/400?random=121',
                    ],
                    [
                        'name' => 'Screwdrivers & Wrenches',
                        'slug' => 'screwdrivers-wrenches',
                        'description' => 'Manual screwdrivers, wrenches, and socket sets.',
                        'sort_order' => 2,
                        'image_url' => 'https://picsum.photos/400/400?random=122',
                    ],
                ],
            ],
            [
                'name' => 'Building Materials',
                'slug' => 'building-materials',
                'description' => 'Construction materials for building and renovation projects.',
                'sort_order' => 3,
                'image_url' => 'https://picsum.photos/400/400?random=103',
                'banner_url' => 'https://picsum.photos/1200/600?random=103',
                'children' => [
                    [
                        'name' => 'Lumber & Wood',
                        'slug' => 'lumber-wood',
                        'description' => 'Framing lumber, plywood, and specialty wood products.',
                        'sort_order' => 1,
                        'image_url' => 'https://picsum.photos/400/400?random=131',
                    ],
                    [
                        'name' => 'Fasteners',
                        'slug' => 'fasteners',
                        'description' => 'Screws, nails, bolts, and construction fasteners.',
                        'sort_order' => 2,
                        'image_url' => 'https://picsum.photos/400/400?random=132',
                    ],
                ],
            ],
            [
                'name' => 'Safety Equipment',
                'slug' => 'safety-equipment',
                'description' => 'Personal protective equipment and safety gear for construction work.',
                'sort_order' => 4,
                'image_url' => 'https://picsum.photos/400/400?random=104',
                'banner_url' => 'https://picsum.photos/1200/600?random=104',
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'description' => 'Electrical components, wiring, and installation tools.',
                'sort_order' => 5,
                'image_url' => 'https://picsum.photos/400/400?random=105',
                'banner_url' => 'https://picsum.photos/1200/600?random=105',
            ],
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'description' => 'Pipes, fittings, and plumbing tools for installation and repair.',
                'sort_order' => 6,
                'image_url' => 'https://picsum.photos/400/400?random=106',
                'banner_url' => 'https://picsum.photos/1200/600?random=106',
            ],
        ];

        foreach ($categories as $categoryData) {
            $this->createCategory($categoryData);
        }
    }

    private function createCategory(array $categoryData, ?int $parentId = null): void
    {
        $category = Category::firstOrCreate(
            ['slug' => $categoryData['slug']],
            [
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'parent_id' => $parentId,
                'sort_order' => $categoryData['sort_order'],
                'is_visible' => true,
            ]
        );

        // Add main image if category was created and doesn't have one
        if ($category && ($category->wasRecentlyCreated || !$category->hasMedia('images')) && isset($categoryData['image_url'])) {
            $this->downloadAndAttachImage($category, $categoryData['image_url'], 'images', $categoryData['name'] . ' Image');
        }

        // Add banner if category was created and doesn't have one
        if ($category && ($category->wasRecentlyCreated || !$category->hasMedia('banner')) && isset($categoryData['banner_url'])) {
            $this->downloadAndAttachImage($category, $categoryData['banner_url'], 'banner', $categoryData['name'] . ' Banner');
        }

        // Create children categories
        if (isset($categoryData['children'])) {
            foreach ($categoryData['children'] as $childData) {
                $this->createCategory($childData, $category->id);
            }
        }
    }

    /**
     * Download image from URL and attach it to the category
     */
    private function downloadAndAttachImage(Category $category, string $imageUrl, string $collection, string $name): void
    {
        try {
            $response = Http::timeout(30)->get($imageUrl);

            if ($response->successful()) {
                $extension = 'jpg';
                $filename = Str::slug($name) . '.' . $extension;

                // Ensure temp directory exists
                $tempDir = storage_path('app/temp');
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $tempPath = $tempDir . '/' . $filename;

                // Save temporary file
                file_put_contents($tempPath, $response->body());

                // Add media to category
                $category
                    ->addMedia($tempPath)
                    ->usingName($name)
                    ->usingFileName($filename)
                    ->toMediaCollection($collection);

                // Clean up temporary file
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                $this->command->info("✓ Added {$collection} image for {$category->name}");
            }
        } catch (\Exception $e) {
            $this->command->warn("✗ Failed to download {$collection} image for {$category->name}: " . $e->getMessage());
        }
    }
}
