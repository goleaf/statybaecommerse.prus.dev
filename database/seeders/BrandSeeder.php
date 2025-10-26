<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Throwable;

final class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = collect([
            ['name' => 'Makita', 'featured' => true],
            ['name' => 'Bosch', 'featured' => true],
            ['name' => 'DeWalt', 'featured' => true],
            ['name' => 'Hilti', 'featured' => true],
            ['name' => 'Festool', 'featured' => false],
            ['name' => 'Milwaukee Tool', 'featured' => true],
            ['name' => 'Metabo', 'featured' => false],
            ['name' => 'Ryobi', 'featured' => false],
            ['name' => 'Stanley Black & Decker', 'featured' => false],
            ['name' => 'Kärcher', 'featured' => false],
        ]);

        /** @var LocalImageGeneratorService $imageGenerator */
        $imageGenerator = app(LocalImageGeneratorService::class);

        $definitions->each(function (array $definition) use ($imageGenerator): void {
            $slug = str($definition['name'])->slug()->toString();

            // Check if brand already exists to maintain idempotency
            $existingBrand = Brand::withoutGlobalScopes()->where('slug', $slug)->first();

            if (! $existingBrand) {
                /** @var Brand $brand */
                $brand = Brand::factory()->create([
                    'name'            => $definition['name'],
                    'slug'            => $slug,
                    'description'     => "{$definition['name']} profesionalūs statybos įrankiai ir sprendimai.",
                    'website'         => 'https://' . $slug . '.lt',
                    'is_enabled'      => true,
                    'is_featured'     => $definition['featured'],
                    'seo_title'       => $definition['name'],
                    'seo_description' => "Atraskite {$definition['name']} įrankių asortimentą statybos projektams.",
                ]);

                // Create translations manually to avoid factory creating additional brands
                $brand->translations()->createMany([
                    [
                        'locale'          => 'lt',
                        'name'            => $definition['name'],
                        'slug'            => $slug,
                        'description'     => "Profesionalūs {$definition['name']} įrankiai Lietuvos rinkai.",
                        'seo_title'       => $definition['name'],
                        'seo_description' => "Patikimi {$definition['name']} įrankiai statyboms Lietuvoje.",
                    ],
                    [
                        'locale'          => 'en',
                        'name'            => $definition['name'] . ' (EN)',
                        'slug'            => $slug . '-en',
                        'description'     => "Professional {$definition['name']} tools for the European market.",
                        'seo_title'       => $definition['name'] . ' (EN)',
                        'seo_description' => "Reliable {$definition['name']} tools for construction projects.",
                    ],
                ]);

                $this->attachGeneratedLogo($brand, $imageGenerator);
            } else {
                $this->attachGeneratedLogo($existingBrand, $imageGenerator);
            }
        });
    }

    private function attachGeneratedLogo(Brand $brand, LocalImageGeneratorService $imageGenerator): void
    {
        if ($brand->hasMedia('logo')) {
            return;
        }

        try {
            $logoPath = $imageGenerator->generateBrandLogo($brand->name);

            if (! file_exists($logoPath)) {
                return;
            }

            $brand
                ->addMedia($logoPath)
                ->withCustomProperties(['source' => 'local_generated'])
                ->usingName($brand->name . ' Logo')
                ->usingFileName(str($brand->slug)->slug()->toString() . '-logo.webp')
                ->toMediaCollection('logo');
        } catch (Throwable $e) {
            report($e);
        } finally {
            if (isset($logoPath) && file_exists($logoPath)) {
                @unlink($logoPath);
            }
        }
    }
}
