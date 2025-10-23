<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private const PRESET_PRODUCTS = [
        'hammer-drill' => [
            'type'              => 'simple',
            'name'              => 'Makita HR2475 smūginis perforatorius',
            'slug'              => 'makita-hr2475-smuginis-perforatorius',
            'sku'               => 'MK-HR2475',
            'price'             => 329.00,
            'short_description' => 'Profesionalus perforatorius betonui ir mūrijimui.',
            'seo_title'         => 'Makita HR2475 smūginis perforatorius',
            'seo_description'   => 'Galingas Makita perforatorius profesionaliems statybos darbams.',
        ],
        'circular-saw' => [
            'type'              => 'simple',
            'name'              => 'DeWalt DWE576K diskinis pjūklas',
            'slug'              => 'dewalt-dwe576k-diskinis-pjuklas',
            'sku'               => 'DW-DWE576K',
            'price'             => 289.00,
            'short_description' => 'Tikslaus pjovimo diskinis pjūklas su kreipiančiaja liniuotė.',
            'seo_title'         => 'DeWalt DWE576K diskinis pjūklas',
            'seo_description'   => 'Patikimas DeWalt diskinis pjūklas tiksliems pjūviams.',
        ],
        'safety-glasses' => [
            'type'              => 'simple',
            'name'              => 'Bosch apsauginiai akiniai UltraClear',
            'slug'              => 'bosch-apsauginiai-akiniai-ultraclear',
            'sku'               => 'BS-GLASS01',
            'price'             => 29.00,
            'short_description' => 'Apsauginiai akiniai su antifog danga ir UV apsauga.',
            'seo_title'         => 'Bosch apsauginiai akiniai UltraClear',
            'seo_description'   => 'Patogūs Bosch apsauginiai akiniai saugiam darbui.',
        ],
    ];

    public function definition(): array
    {
        $lithuanianProducts = [
            // Elektriniai įrankiai
            'Elektrinis perforatorius',
            'Kampuotasis šlifuoklis',
            'Elektrinis pjūklas',
            'Suktuvas-gręžtuvas',
            'Vibracinė šlifavimo mašina',
            'Elektrinė disko pjovimo mašina',
            'Planuoklis',
            'Frezeris',
            'Elektrinė grandinės pjovimo mašina',
            'Smūginis gręžtuvas',
            // Rankiniai įrankiai
            'Profesionalus plaktukas',
            'Statybinė gulsčioji',
            'Ruletė 10m',
            'Universalus peilis',
            'Raktų komplektas',
            'Atsuktuvų rinkinys',
            'Replės elektrikui',
            'Metalinis liniuotė',
            'Kaltai medžiui',
            'Kampuotė',
            // Statybinės medžiagos
            'Cemento mišinys',
            'Gipso plokštės',
            'Termoizoliacijos plokštės',
            'Hidroizoliacijos plėvelė',
            'Statybinė putos',
            'Akrilo hermetikas',
            'Gruntavimo skystis',
            'Fasadiniai dažai',
            'Klijų mišinys plytelėms',
            'Betono priedas',
            // Saugos priemonės
            'Apsauginiai akiniai',
            'Darbo pirštinės',
            'Apsauginis šalmas',
            'Apsauginiai batai',
            'Respiratorius',
            'Ausų apsaugos',
            'Atsvarinis diržas',
            'Šviečianti liemenė',
            'Pirmos pagalbos vaistinėlė',
            'Apsauginė kaukė',
        ];

        $name = $this->faker->randomElement($lithuanianProducts);
        $basePrice = $this->faker->randomFloat(2, 5, 2000);
        $salePrice = $this->faker->boolean(25) ? $basePrice * 0.8 : null;

        return [
            'type'                => 'simple',
            'name'                => $name,
            'slug'                => Str::slug($name . '-' . $this->faker->unique()->randomNumber()),
            'sku'                 => 'LT-' . strtoupper(Str::random(8)),
            'description'         => $this->generateLithuanianDescription($name),
            'short_description'   => $this->generateShortDescription($name),
            'price'               => $basePrice,
            'sale_price'          => $salePrice,
            'brand_id'            => Brand::factory(),
            'stock_quantity'      => $this->faker->numberBetween(0, 200),
            'low_stock_threshold' => $this->faker->numberBetween(5, 20),
            'weight'              => $this->faker->randomFloat(2, 0.1, 25.0),
            'length'              => $this->faker->randomFloat(2, 5, 200),
            'width'               => $this->faker->randomFloat(2, 5, 200),
            'height'              => $this->faker->randomFloat(2, 2, 100),
            // Default products should satisfy the published/visible scopes used by the
            // API layer so contract tests can rely on generated fixtures without
            // additional state tweaks.
            'is_visible'      => true,
            'is_enabled'      => true,
            'is_featured'     => false,
            'manage_stock'    => $this->faker->boolean(85),
            'status'          => 'published',
            'seo_title'       => $name . ' - Profesionalūs statybos įrankiai',
            'seo_description' => 'Pirkite ' . strtolower($name) . ' geriausia kaina Lietuvoje. Greitas pristatymas visoje šalyje.',
            'published_at'    => now()->subDays(3),
        ];
    }

    private function generateLithuanianDescription(string $productName): string
    {
        $descriptions = [
            "<p>Aukštos kokybės {$productName} profesionaliems statybos darbams. Patikimas ir ilgalaikis sprendimas jūsų projektams.</p><p>Tinka tiek profesionaliems statybininkams, tiek namų meistrams. Garantuojame kokybę ir patikimumą.</p>",
            "<p>Profesionalus {$productName} skirtas intensyviam naudojimui statybvietėse. Ergonomiškas dizainas ir aukšta kokybė.</p><p>Idealiai tinka namų statybai, renovacijai ir remonto darbams.</p>",
            "<p>Patikimas {$productName} su išplėsta garantija. Sukurtas atsižvelgiant į Lietuvos statybininkų poreikius.</p><p>Lengvai naudojamas, saugus ir efektyvus darbo įrankis.</p>",
            "<p>Inovatyvus {$productName} su pažangiomis funkcijomis. Padidins jūsų darbo efektyvumą ir kokybę.</p><p>Sertifikuotas pagal ES standartus, tinka profesionaliems projektams.</p>",
            "<p>Universalus {$productName} daugeliui statybos darbų. Kompaktiškas, patogus ir funkcionalus.</p><p>Puikiai tinka tiek vidaus, tiek lauko darbams. Atsparumas lietuviškoms oro sąlygoms.</p>",
        ];

        return $this->faker->randomElement($descriptions);
    }

    private function generateShortDescription(string $productName): string
    {
        $shorts = [
            "Profesionalus {$productName} aukščiausios kokybės",
            "Patikimas {$productName} statybos darbams",
            "Efektyvus {$productName} su garantija",
            "Universalus {$productName} profesionalams",
            "Kokybiškas {$productName} geriausia kaina",
        ];

        return $this->faker->randomElement($shorts);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            // Skip media for now - will be added manually or via admin
        });
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now()->subDays(3),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function hammerDrill(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('hammer-drill'));
    }

    public function circularSaw(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('circular-saw'));
    }

    public function safetyGlasses(): static
    {
        return $this->state(fn (array $attributes) => $this->preset('safety-glasses'));
    }

    private function preset(string $key): array
    {
        $preset = self::PRESET_PRODUCTS[$key] ?? [];

        return array_merge([
            'description'         => $this->generateLithuanianDescription($preset['name'] ?? 'Produktas'),
            'weight'              => 1.0,
            'length'              => 10.0,
            'width'               => 10.0,
            'height'              => 10.0,
            'manage_stock'        => true,
            'stock_quantity'      => 25,
            'low_stock_threshold' => 5,
        ], $preset);
    }
}
