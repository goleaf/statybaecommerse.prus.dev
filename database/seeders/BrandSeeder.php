<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
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
        $brands = [
            // House Builders & Construction Companies
            [
                'name' => 'Pulte Homes',
                'slug' => 'pulte-homes',
                'description' => 'Leading homebuilding company offering quality new homes with innovative designs and energy-efficient features.',
                'website' => 'https://www.pulte.com',
                'logo_url' => 'https://picsum.photos/400/400?random=1',
                'banner_url' => 'https://picsum.photos/1200/600?random=1',
                'is_featured' => true,
            ],
            [
                'name' => 'D.R. Horton',
                'slug' => 'dr-horton',
                'description' => "America's largest homebuilder, constructing quality homes in desirable locations nationwide.",
                'website' => 'https://www.drhorton.com',
                'logo_url' => 'https://picsum.photos/400/400?random=2',
                'banner_url' => 'https://picsum.photos/1200/600?random=2',
            ],
            [
                'name' => 'Lennar Corporation',
                'slug' => 'lennar',
                'description' => 'Homebuilder focused on creating exceptional communities and innovative home designs.',
                'website' => 'https://www.lennar.com',
                'logo_url' => 'https://picsum.photos/400/400?random=3',
                'banner_url' => 'https://picsum.photos/1200/600?random=3',
            ],
            [
                'name' => 'KB Home',
                'slug' => 'kb-home',
                'description' => 'Homebuilding company offering personalized homes built to order in desirable neighborhoods.',
                'website' => 'https://www.kbhome.com',
                'logo_url' => 'https://picsum.photos/400/400?random=4',
                'banner_url' => 'https://picsum.photos/1200/600?random=4',
            ],
            [
                'name' => 'Toll Brothers',
                'slug' => 'toll-brothers',
                'description' => 'Luxury homebuilder specializing in premium communities and custom home designs.',
                'website' => 'https://www.tollbrothers.com',
                'logo_url' => 'https://picsum.photos/400/400?random=5',
                'banner_url' => 'https://picsum.photos/1200/600?random=5',
            ],
            // Construction & Repair Tools
            [
                'name' => 'DeWalt',
                'slug' => 'dewalt',
                'description' => 'Professional power tools and hand tools for construction, manufacturing and woodworking.',
                'website' => 'https://www.dewalt.com',
                'logo_url' => 'https://picsum.photos/400/400?random=6',
                'banner_url' => 'https://picsum.photos/1200/600?random=6',
                'is_featured' => true,
            ],
            [
                'name' => 'Milwaukee Tool',
                'slug' => 'milwaukee-tool',
                'description' => 'Heavy-duty power tools, hand tools, and accessories for professional tradespeople.',
                'website' => 'https://www.milwaukeetool.com',
                'logo_url' => 'https://picsum.photos/400/400?random=7',
                'banner_url' => 'https://picsum.photos/1200/600?random=7',
                'is_featured' => true,
            ],
            [
                'name' => 'Makita',
                'slug' => 'makita',
                'description' => 'Japanese manufacturer of power tools, outdoor power equipment, and pneumatic tools.',
                'website' => 'https://www.makita.com',
                'logo_url' => 'https://picsum.photos/400/400?random=8',
                'banner_url' => 'https://picsum.photos/1200/600?random=8',
            ],
            [
                'name' => 'Bosch',
                'slug' => 'bosch',
                'description' => 'Professional power tools and measuring tools for construction and renovation work.',
                'website' => 'https://www.bosch.com',
                'logo_url' => 'https://picsum.photos/400/400?random=9',
                'banner_url' => 'https://picsum.photos/1200/600?random=9',
            ],
            [
                'name' => 'Stanley Black & Decker',
                'slug' => 'stanley-black-decker',
                'description' => 'Tools and storage solutions for professional contractors and DIY enthusiasts.',
                'website' => 'https://www.stanleyblackanddecker.com',
                'logo_url' => 'https://picsum.photos/400/400?random=10',
                'banner_url' => 'https://picsum.photos/1200/600?random=10',
            ],
            [
                'name' => 'Ryobi',
                'slug' => 'ryobi',
                'description' => 'Power tools and outdoor equipment for homeowners and professionals.',
                'website' => 'https://www.ryobitools.com',
                'logo_url' => 'https://picsum.photos/400/400?random=11',
                'banner_url' => 'https://picsum.photos/1200/600?random=11',
            ],
            [
                'name' => 'Craftsman',
                'slug' => 'craftsman',
                'description' => 'Hand tools, power tools, and lawn and garden equipment for home improvement projects.',
                'website' => 'https://www.craftsman.com',
                'logo_url' => 'https://picsum.photos/400/400?random=12',
                'banner_url' => 'https://picsum.photos/1200/600?random=12',
            ],
            [
                'name' => 'Hilti',
                'slug' => 'hilti',
                'description' => 'Professional-grade tools, technology, software and services for construction industry.',
                'website' => 'https://www.hilti.com',
                'logo_url' => 'https://picsum.photos/400/400?random=13',
                'banner_url' => 'https://picsum.photos/1200/600?random=13',
            ],
            [
                'name' => 'Festool',
                'slug' => 'festool',
                'description' => 'Premium power tools and accessories for woodworking and construction professionals.',
                'website' => 'https://www.festool.com',
                'logo_url' => 'https://picsum.photos/400/400?random=14',
                'banner_url' => 'https://picsum.photos/1200/600?random=14',
            ],
            [
                'name' => 'Ridgid',
                'slug' => 'ridgid',
                'description' => 'Professional plumbing tools, drain cleaning equipment, and construction tools.',
                'website' => 'https://www.ridgid.com',
                'logo_url' => 'https://picsum.photos/400/400?random=15',
                'banner_url' => 'https://picsum.photos/1200/600?random=15',
            ],
        ];

        foreach ($brands as $brandData) {
            $brand = Brand::firstOrCreate(
                ['slug' => $brandData['slug']],
                [
                    'name' => $brandData['name'],
                    'description' => $brandData['description'],
                    'website' => $brandData['website'],
                    'is_enabled' => true,
                    'is_featured' => $brandData['is_featured'] ?? false,
                ]
            );

            // Upsert translations for all supported locales
            $locales = $this->supportedLocales();
            $now = now();
            $trRows = [];
            foreach ($locales as $loc) {
                $name = $this->translateLike($brandData['name'], $loc);
                $trRows[] = [
                    'brand_id' => $brand->id,
                    'locale' => $loc,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => $this->translateLike($brandData['description'], $loc),
                    'seo_title' => $name,
                    'seo_description' => $this->translateLike('Originali statybos prekių gamintojo informacija.', $loc),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('brand_translations')->upsert(
                $trRows,
                ['brand_id', 'locale'],
                ['name', 'slug', 'description', 'seo_title', 'seo_description', 'updated_at']
            );

            // Add logo if brand was created and doesn't have one
            if ($brand->wasRecentlyCreated || ! $brand->hasMedia('logo')) {
                $this->downloadAndAttachImage($brand, $brandData['logo_url'], 'logo', $brandData['name'].' Logo');
            }

            // Add banner if brand was created and doesn't have one
            if ($brand->wasRecentlyCreated || ! $brand->hasMedia('banner')) {
                $this->downloadAndAttachImage($brand, $brandData['banner_url'], 'banner', $brandData['name'].' Banner');
            }
        }
    }

    /**
     * Download image from URL and attach it to the brand
     */
    private function downloadAndAttachImage(Brand $brand, string $imageUrl, string $collection, string $name): void
    {
        try {
            // Skip image generation to avoid memory issues during seeding
            $this->command->info("⏭ Skipped {$collection} image generation for {$brand->name} (memory optimization)");

            // TODO: Re-enable image generation after fixing memory issues
            // Generate local WebP image based on collection type
            // $imagePath = match ($collection) {
            //     'logo' => $this->imageGenerator->generateBrandLogo($brand->name),
            //     'banner' => $this->imageGenerator->generateBrandBanner($brand->name),
            //     default => $this->imageGenerator->generateBrandLogo($brand->name)
            // };

            // if (file_exists($imagePath)) {
            //     $filename = Str::slug($name).'.webp';

            //     // Add media to brand
            //     $brand
            //         ->addMedia($imagePath)
            //         ->withCustomProperties(['source' => 'local_generated'])
            //         ->usingName($name)
            //         ->usingFileName($filename)
            //         ->toMediaCollection($collection);

            //     // Clean up temporary file
            //     if (file_exists($imagePath)) {
            //         unlink($imagePath);
            //     }

            //     $this->command->info("✓ Generated {$collection} WebP image for {$brand->name}");
            // } else {
            //     $this->command->warn("✗ Failed to generate {$collection} image for {$brand->name}");
            // }
        } catch (\Exception $e) {
            $this->command->warn("✗ Failed to generate {$collection} image for {$brand->name}: ".$e->getMessage());
        }
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()->unique()->values()->all();
    }

    private function translateLike(string $text, string $locale): string
    {
        return match ($locale) {
            'lt' => $text,
            'en' => $text.' (EN)',
            'ru' => $text.' (RU)',
            'de' => $text.' (DE)',
            default => $text.' ('.strtoupper($locale).')',
        };
    }
}
