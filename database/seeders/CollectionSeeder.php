<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CollectionTranslation;
use App\Models\Collection;
use App\Services\Images\LocalImageGeneratorService;
use Database\Seeders\Data\HouseBuilderCollections;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService;
    }

    public function run(): void
    {
        $definitions = HouseBuilderCollections::collections();
        $locales = $this->supportedLocales();

        foreach ($definitions as $slug => $definition) {
            $primaryTranslation = $definition['translations']['en'];

            // Check if collection already exists to maintain idempotency
            $existingCollection = Collection::where('slug', $slug)->first();

            if ($existingCollection) {
                $existingCollection->update([
                    'name' => $primaryTranslation['name'],
                    'sort_order' => $definition['sort_order'],
                    'is_visible' => true,
                    'is_automatic' => $definition['is_automatic'] ?? false,
                    'display_type' => $definition['display_type'] ?? 'grid',
                ]);
                $collection = $existingCollection;
            } else {
                // Use factory to create collection
                $collection = Collection::factory()
                    ->state([
                        'slug' => $slug,
                        'name' => $primaryTranslation['name'],
                        'sort_order' => $definition['sort_order'],
                        'is_visible' => true,
                        'is_automatic' => $definition['is_automatic'] ?? false,
                        'display_type' => $definition['display_type'] ?? 'grid',
                    ])
                    ->create();
            }

            foreach ($locales as $locale) {
                $translation = $definition['translations'][$locale] ?? $primaryTranslation;

                $existingTranslation = CollectionTranslation::where([
                    'collection_id' => $collection->id,
                    'locale' => $locale,
                ])->first();

                if ($existingTranslation) {
                    $existingTranslation->update([
                        'name' => $translation['name'],
                        'slug' => $locale === 'lt' ? $slug : $slug . '-' . $locale,
                        'description' => $translation['description'],
                        'meta_title' => $translation['name'] . ' | ' . config('app.name'),
                        'meta_description' => $translation['description'],
                        'meta_keywords' => $translation['keywords'] ?? [],
                    ]);
                } else {
                    // Use factory to create translation
                    CollectionTranslation::factory()
                        ->for($collection)
                        ->state([
                            'locale' => $locale,
                            'name' => $translation['name'],
                            'slug' => $locale === 'lt' ? $slug : $slug . '-' . $locale,
                            'description' => $translation['description'],
                            'meta_title' => $translation['name'] . ' | ' . config('app.name'),
                            'meta_description' => $translation['description'],
                            'meta_keywords' => $translation['keywords'] ?? [],
                        ])
                        ->create();
                }
            }

            $this->ensureCollectionMedia($collection, $definition['image_text'] ?? $primaryTranslation['name']);

            $this->command?->info(sprintf('CollectionSeeder: prepared "%s" collection.', $primaryTranslation['name']));
        }
    }

    private function ensureCollectionMedia(Collection $collection, string $label): void
    {
        try {
            if (!$collection->hasMedia('images')) {
                $imagePath = $this->imageGenerator->generateCollectionImage($label);
                $collection
                    ->addMedia($imagePath)
                    ->withCustomProperties(['source' => 'generated'])
                    ->usingName($label . ' Image')
                    ->toMediaCollection('images');

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            if (!$collection->hasMedia('banner')) {
                $bannerPath = $this->imageGenerator->generateCollectionImage($label . ' Banner');
                $collection
                    ->addMedia($bannerPath)
                    ->withCustomProperties(['source' => 'generated'])
                    ->usingName($label . ' Banner')
                    ->toMediaCollection('banner');

                if (file_exists($bannerPath)) {
                    unlink($bannerPath);
                }
            }
        } catch (\Throwable $exception) {
            $this->command?->warn('CollectionSeeder: failed to generate imagery for ' . $collection->slug . ': ' . $exception->getMessage());
        }
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en,ru,de')))
            ->map(fn($locale) => trim($locale))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
