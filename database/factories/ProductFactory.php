<?php declare(strict_types=1);

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
            'Apsauginė kaukė'
        ];

        $name = $this->faker->randomElement($lithuanianProducts);
        $basePrice = $this->faker->randomFloat(2, 5, 2000);
        $salePrice = $this->faker->boolean(25) ? $basePrice * 0.8 : null;

        return [
            'type' => 'simple',
            'name' => $name,
            'slug' => Str::slug($name . '-' . $this->faker->unique()->randomNumber()),
            'sku' => 'LT-' . strtoupper(Str::random(8)),
            'description' => $this->generateLithuanianDescription($name),
            'short_description' => $this->generateShortDescription($name),
            'price' => $basePrice,
            'sale_price' => $salePrice,
            'brand_id' => Brand::factory(),
            'stock_quantity' => $this->faker->numberBetween(0, 200),
            'low_stock_threshold' => $this->faker->numberBetween(5, 20),
            'weight' => $this->faker->randomFloat(2, 0.1, 25.0),
            'length' => $this->faker->randomFloat(2, 5, 200),
            'width' => $this->faker->randomFloat(2, 5, 200),
            'height' => $this->faker->randomFloat(2, 2, 100),
            'is_visible' => true,
            'is_featured' => $this->faker->boolean(15),
            'manage_stock' => $this->faker->boolean(85),
            'status' => $this->faker->randomElement(['published', 'published', 'published', 'draft']),
            'seo_title' => $name . ' - Profesionalūs statybos įrankiai',
            'seo_description' => 'Pirkite ' . strtolower($name) . ' geriausia kaina Lietuvoje. Greitas pristatymas visoje šalyje.',
            'published_at' => $this->faker->dateTimeBetween('-30 days', '+5 days'),
        ];
    }

    private function generateLithuanianDescription(string $productName): string
    {
        $descriptions = [
            "<p>Aukštos kokybės {$productName} profesionaliems statybos darbams. Patikimas ir ilgalaikis sprendimas jūsų projektams.</p><p>Tinka tiek profesionaliems statybininkams, tiek namų meistrams. Garantuojame kokybę ir patikimumą.</p>",
            "<p>Profesionalus {$productName} skirtas intensyviam naudojimui statybvietėse. Ergonomiškas dizainas ir aukšta kokybė.</p><p>Idealiai tinka namų statybai, renovacijai ir remonto darbams.</p>",
            "<p>Patikimas {$productName} su išplėsta garantija. Sukurtas atsižvelgiant į Lietuvos statybininkų poreikius.</p><p>Lengvai naudojamas, saugus ir efektyvus darbo įrankis.</p>",
            "<p>Inovatyvus {$productName} su pažangiomis funkcijomis. Padidins jūsų darbo efektyvumą ir kokybę.</p><p>Sertifikuotas pagal ES standartus, tinka profesionaliems projektams.</p>",
            "<p>Universalus {$productName} daugeliui statybos darbų. Kompaktiškas, patogus ir funkcionalus.</p><p>Puikiai tinka tiek vidaus, tiek lauko darbams. Atsparumas lietuviškoms oro sąlygoms.</p>"
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
            "Kokybiškas {$productName} geriausia kaina"
        ];

        return $this->faker->randomElement($shorts);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            // Skip media for now - will be added manually or via admin
        });
    }
}