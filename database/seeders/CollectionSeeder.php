<?php

namespace Database\Seeders;

use App\Models\Translations\CollectionTranslation;
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
        $locales = $this->supportedLocales();

        $collections = [
            [
                'slug' => 'new-home-essentials',
                'name' => [
                    'lt' => 'Naujo namo esmės',
                    'en' => 'New Home Essentials'
                ],
                'description' => [
                    'lt' => 'Viskas, ko reikia pradėti naujo namo statybos projektą.',
                    'en' => 'Everything you need to get started with your new home construction project.'
                ],
                'sort_order' => 1,
                'image_url' => 'https://picsum.photos/600/600?random=201',
                'banner_url' => 'https://picsum.photos/1200/600?random=201',
            ],
            [
                'slug' => 'professional-contractor-tools',
                'name' => [
                    'lt' => 'Profesionalūs rangovo įrankiai',
                    'en' => 'Professional Contractor Tools'
                ],
                'description' => [
                    'lt' => 'Aukštos kokybės įrankiai, kuriais pasitiki profesionalūs rangovai ir statybininkai.',
                    'en' => 'High-quality tools trusted by professional contractors and builders.'
                ],
                'sort_order' => 2,
                'image_url' => 'https://picsum.photos/600/600?random=202',
                'banner_url' => 'https://picsum.photos/1200/600?random=202',
            ],
            [
                'slug' => 'diy-home-improvement',
                'name' => [
                    'lt' => 'DIY namų gerinimas',
                    'en' => 'DIY Home Improvement'
                ],
                'description' => [
                    'lt' => 'Įrankiai ir medžiagos, puikiai tinkantys savaitgalio DIY projektams ir namų gerinimui.',
                    'en' => 'Tools and materials perfect for weekend DIY projects and home improvements.'
                ],
                'sort_order' => 3,
                'image_url' => 'https://picsum.photos/600/600?random=203',
                'banner_url' => 'https://picsum.photos/1200/600?random=203',
            ],
            [
                'slug' => 'outdoor-landscaping',
                'name' => [
                    'lt' => 'Lauko ir želdinių dizainas',
                    'en' => 'Outdoor & Landscaping'
                ],
                'description' => [
                    'lt' => 'Įrankiai ir įranga lauko statyboms ir želdinių dizaino projektams.',
                    'en' => 'Tools and equipment for outdoor construction and landscaping projects.'
                ],
                'sort_order' => 4,
                'image_url' => 'https://picsum.photos/600/600?random=204',
                'banner_url' => 'https://picsum.photos/1200/600?random=204',
            ],
            [
                'slug' => 'renovation-specialists',
                'name' => [
                    'lt' => 'Rekonstrukcijos specialistai',
                    'en' => 'Renovation Specialists'
                ],
                'description' => [
                    'lt' => 'Specializuoti įrankiai ir medžiagos namų rekonstrukcijai ir perplanavimui.',
                    'en' => 'Specialized tools and materials for home renovation and remodeling.'
                ],
                'sort_order' => 5,
                'image_url' => 'https://picsum.photos/600/600?random=205',
                'banner_url' => 'https://picsum.photos/1200/600?random=205',
            ],
            [
                'slug' => 'energy-efficient-solutions',
                'name' => [
                    'lt' => 'Energijos taupymo sprendimai',
                    'en' => 'Energy Efficient Solutions'
                ],
                'description' => [
                    'lt' => 'Ekologiškos statybos medžiagos ir energijos taupymo statybos sprendimai.',
                    'en' => 'Eco-friendly building materials and energy-saving construction solutions.'
                ],
                'sort_order' => 6,
                'image_url' => 'https://picsum.photos/600/600?random=206',
                'banner_url' => 'https://picsum.photos/1200/600?random=206',
            ],
        ];

        foreach ($collections as $collectionData) {
            // Extract translations and set default name
            $translations = [
                'name' => $collectionData['name'] ?? [],
                'description' => $collectionData['description'] ?? [],
            ];
            $defaultName = $collectionData['name']['en'] ?? $collectionData['slug'];
            
            $collection = Collection::firstOrCreate(
                ['slug' => $collectionData['slug']],
                [
                    'name' => $defaultName,
                    'sort_order' => $collectionData['sort_order'],
                    'is_visible' => true,
                    'is_automatic' => false,
                ]
            );

            // Create translations for each locale
            foreach ($locales as $locale) {
                CollectionTranslation::updateOrCreate([
                    'collection_id' => $collection->id,
                    'locale' => $locale,
                ], [
                    'name' => $translations['name'][$locale] ?? $translations['name']['en'] ?? $collectionData['slug'],
                    'description' => $translations['description'][$locale] ?? $translations['description']['en'] ?? '',
                    'slug' => $collectionData['slug'] . ($locale !== 'lt' ? '-' . $locale : ''),
                ]);
            }

            // Add main image if collection was created and doesn't have one
            $collectionName = $translations['name']['en'] ?? $collectionData['slug'];
            if (($collection->wasRecentlyCreated || !$collection->hasMedia('images')) && isset($collectionData['image_url'])) {
                $this->downloadAndAttachImage($collection, $collectionData['image_url'], 'images', $collectionName . ' Image');
            }

            // Add banner if collection was created and doesn't have one
            if (($collection->wasRecentlyCreated || !$collection->hasMedia('banner')) && isset($collectionData['banner_url'])) {
                $this->downloadAndAttachImage($collection, $collectionData['banner_url'], 'banner', $collectionName . ' Banner');
            }
        }

        $this->command?->info('CollectionSeeder: seeded collections with translations (locales: ' . implode(',', $locales) . ').');
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

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
