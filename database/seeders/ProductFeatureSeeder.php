<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

final class ProductFeatureSeeder extends Seeder
{
    private const FEATURE_TEMPLATES = [
        'electronics' => [
            'specification' => [
                'weight' => ['Lengvas', 'Sunkus', 'Ypač lengvas'],
                'dimensions' => ['Kompaktiškas', 'Didelis', 'Nešiojamas'],
                'battery_life' => ['Ilgaamžė', 'Greitas įkrovimas', 'Pratęsta veikimo trukmė'],
                'connectivity' => ['WiFi', 'Bluetooth', 'USB-C', 'Belaidis'],
                'screen_size' => ['Mažas', 'Vidutinis', 'Didelis', 'Ypač didelis'],
            ],
            'benefit' => [
                'energy_efficient' => ['Energiją taupantis', 'Ekologiškas', 'Mažai vartojantis'],
                'user_friendly' => ['Lengvai naudojamas', 'Intuityvus', 'Pradedantiesiems'],
                'durable' => ['Ilgaamžis', 'Patvarus', 'Patikimas'],
            ],
            'technical' => [
                'processor' => ['Greitas', 'Efektyvus', 'Didelio našumo'],
                'memory' => ['Didelės talpos', 'Sparti', 'Išplečiama'],
                'storage' => ['Talpi', 'Greitas perdavimas', 'Saugus'],
            ],
            'performance' => [
                'speed' => ['Greitas', 'Ypač greitas', 'Žaibiškas'],
                'quality' => ['Aukštos kokybės', 'Premium', 'Profesionalus'],
                'efficiency' => ['Optimizuotas', 'Supaprastintas', 'Patobulintas'],
            ],
        ],
        'clothing' => [
            'specification' => [
                'material' => ['Medvilnė', 'Poliesteris', 'Vilna', 'Šilkas', 'Linas'],
                'size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'color' => ['Juoda', 'Balta', 'Mėlyna', 'Raudona', 'Žalia', 'Įvairiaspalvė'],
                'fit' => ['Aptemptas', 'Įprastas', 'Laisvas', 'Perdydis'],
            ],
            'benefit' => [
                'comfort' => ['Patogus', 'Minkštas', 'Kvėpuojantis'],
                'style' => ['Madingas', 'Stilingas', 'Klasikinis', 'Šiuolaikiškas'],
                'versatility' => ['Universalus', 'Daugiafunkcis', 'Lankstus'],
            ],
            'technical' => [
                'care_instructions' => ['Skalbti mašina', 'Skalbti rankomis', 'Valyti sausu būdu'],
                'fabric_technology' => ['Drėgmę sugeriantis', 'Tamprus', 'Antibakterinis'],
            ],
        ],
        'home_garden' => [
            'specification' => [
                'dimensions' => ['Kompaktiškas', 'Standartinis', 'Didelis'],
                'material' => ['Medis', 'Metalas', 'Plastikas', 'Stiklas', 'Keramika'],
                'capacity' => ['Maža', 'Vidutinė', 'Didelė', 'Ypač didelė'],
            ],
            'benefit' => [
                'durability' => ['Ilgaamžiškas', 'Atsparus orams', 'Tvirtas'],
                'aesthetics' => ['Gražus', 'Elegantiškas', 'Modernus', 'Tradicinis'],
                'functionality' => ['Praktiškas', 'Daugiafunkcis', 'Taupantis vietą'],
            ],
            'technical' => [
                'installation' => ['Lengvas montavimas', 'Reikia specialisto', 'Tinka DIY'],
                'maintenance' => ['Mažai priežiūros', 'Lengvai valomas', 'Savaime išsivalantis'],
            ],
        ],
    ];

    private const GENERIC_FEATURES = [
        'warranty' => ['1 metų garantija', '2 metų garantija', '3 metų garantija', '5 metų garantija', 'Viso gyvenimo garantija'],
        'shipping' => ['Nemokamas pristatymas', 'Skubi pristatymo paslauga', 'Standartinis pristatymas'],
        'availability' => ['Sandėlyje', 'Ribotas kiekis', 'Išankstinis užsakymas'],
        'rating' => ['5 žvaigždutės', 'Aukštai įvertintas', 'Klientų favoritas'],
        'popularity' => ['Perkamiausias', 'Populiarus pasirinkimas', 'Tendencijas kuriantis'],
    ];

    private const FEATURE_TYPES = ['specification', 'benefit', 'feature', 'technical', 'performance'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()->limit(15)->get();

        if ($products->isEmpty()) {
            $this->command?->warn('Nerasta produktų. Pirmiausia paleiskite ProductSeeder.');

            return;
        }

        foreach ($products as $product) {
            if (ProductFeature::query()->where('product_id', $product->id)->exists()) {
                continue;
            }

            $categoryKey = Arr::random(array_keys(self::FEATURE_TEMPLATES));
            $featureCount = fake()->numberBetween(8, 15);

            ProductFeature::factory()
                ->count($featureCount)
                ->for($product)
                ->state(fn () => $this->featureState($categoryKey))
                ->create();
        }
    }

    private function featureState(string $categoryKey): array
    {
        $categoryFeatures = self::FEATURE_TEMPLATES[$categoryKey] ?? [];

        if (empty($categoryFeatures) || fake()->boolean(25)) {
            return $this->genericFeatureState();
        }

        $featureType = Arr::random(array_keys($categoryFeatures));
        $featureOptions = $categoryFeatures[$featureType];
        $featureKey = Arr::random(array_keys($featureOptions));
        $featureValue = Arr::random($featureOptions[$featureKey]);

        return [
            'feature_type' => $featureType,
            'feature_key' => $featureKey,
            'feature_value' => $featureValue,
            'weight' => $this->generateWeight($featureType),
        ];
    }

    private function genericFeatureState(): array
    {
        $featureKey = Arr::random(array_keys(self::GENERIC_FEATURES));
        $featureValue = Arr::random(self::GENERIC_FEATURES[$featureKey]);
        $featureType = Arr::random(self::FEATURE_TYPES);

        return [
            'feature_type' => $featureType,
            'feature_key' => $featureKey,
            'feature_value' => $featureValue,
            'weight' => $this->generateWeight($featureType),
        ];
    }

    /**
     * Generate weight based on feature type
     */
    private function generateWeight(string $featureType): float
    {
        return match ($featureType) {
            'specification' => fake()->numberBetween(80, 100) / 100,
            'benefit' => fake()->numberBetween(70, 95) / 100,
            'technical' => fake()->numberBetween(60, 90) / 100,
            'performance' => fake()->numberBetween(75, 100) / 100,
            default => fake()->numberBetween(50, 85) / 100,
        };
    }
}
