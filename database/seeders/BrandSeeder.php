<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Translations\BrandTranslation;
use Illuminate\Database\Seeder;

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
            ['name' => 'Husqvarna', 'featured' => false],
            ['name' => 'Würth', 'featured' => false],
        ]);

        $definitions->each(function (array $definition): void {
            $brand = Brand::factory()->create([
                'name' => $definition['name'],
                'slug' => str($definition['name'])->slug()->toString(),
                'description' => "{$definition['name']} profesionalūs statybos įrankiai ir sprendimai.",
                'website' => 'https://'.str($definition['name'])->slug()->toString().'.lt',
                'is_enabled' => true,
                'is_featured' => $definition['featured'],
                'seo_title' => $definition['name'],
                'seo_description' => "Atraskite {$definition['name']} įrankių asortimentą statybos projektams.",
            ]);

            BrandTranslation::factory()->createMany([
                [
                    'brand_id' => $brand->getKey(),
                    'locale' => 'lt',
                    'name' => $definition['name'],
                    'slug' => str($definition['name'])->slug()->toString(),
                    'description' => "Profesionalūs {$definition['name']} įrankiai Lietuvos rinkai.",
                    'seo_title' => $definition['name'],
                    'seo_description' => "Patikimi {$definition['name']} įrankiai statyboms Lietuvoje.",
                ],
                [
                    'brand_id' => $brand->getKey(),
                    'locale' => 'en',
                    'name' => $definition['name'].' (EN)',
                    'slug' => str($definition['name'])->slug()->toString().'-en',
                    'description' => "Professional {$definition['name']} tools for the European market.",
                    'seo_title' => $definition['name'].' (EN)',
                    'seo_description' => "Reliable {$definition['name']} tools for construction projects.",
                ],
            ]);
        });
    }
}
