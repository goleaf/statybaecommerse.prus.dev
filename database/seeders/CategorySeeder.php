<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService;
    }

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = [];

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

        // Upsert translations for all supported locales
        $locales = $this->supportedLocales();
        $now = now();
        $trRows = [];
        foreach ($locales as $loc) {
            $name = $this->translateLike($categoryData['name'], $loc);
            $trRows[] = [
                'category_id' => $category->id,
                'locale' => $loc,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'description' => $this->translateLike($categoryData['description'], $loc),
                'seo_title' => $name,
                'seo_description' => $this->translateLike('Statybinių prekių kategorija.', $loc),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        \Illuminate\Support\Facades\DB::table('category_translations')->upsert(
            $trRows,
            ['category_id', 'locale'],
            ['name', 'slug', 'description', 'seo_title', 'seo_description', 'updated_at']
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
     * Generate local WebP image and attach it to the category
     */
    private function downloadAndAttachImage(Category $category, string $imageUrl, string $collection, string $name): void
    {
        try {
            // Generate local WebP image instead of downloading
            $imagePath = $this->imageGenerator->generateCategoryImage($category->name);

            if (file_exists($imagePath)) {
                $filename = Str::slug($name) . '.webp';

                // Add media to category
                $category
                    ->addMedia($imagePath)
                    ->withCustomProperties(['source' => 'local_generated'])
                    ->usingName($name)
                    ->usingFileName($filename)
                    ->toMediaCollection($collection);

                // Clean up temporary file
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $this->command->info("✓ Generated {$collection} WebP image for {$category->name}");
            } else {
                $this->command->warn("✗ Failed to generate {$collection} image for {$category->name}");
            }
        } catch (\Exception $e) {
            $this->command->warn("✗ Failed to generate {$collection} image for {$category->name}: " . $e->getMessage());
        }
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt')))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function translateLike(string $text, string $locale): string
    {
        return match ($locale) {
            'lt' => $text,
            'en' => $text . ' (EN)',
            'ru' => $text . ' (RU)',
            'de' => $text . ' (DE)',
            default => $text . ' (' . strtoupper($locale) . ')',
        };
    }
}
