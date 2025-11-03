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
                'weight'       => ['Lengvas', 'Sunkus', 'Ypač lengvas'],
                'dimensions'   => ['Kompaktiškas', 'Didelis', 'Nešiojamas'],
                'battery_life' => ['Ilgaamžė', 'Greitas įkrovimas', 'Pratęsta veikimo trukmė'],
                'connectivity' => ['WiFi', 'Bluetooth', 'USB-C', 'Belaidis'],
                'screen_size'  => ['Mažas', 'Vidutinis', 'Didelis', 'Ypač didelis'],
            ],
            'benefit' => [
                'energy_efficient' => ['Energiją taupantis', 'Ekologiškas', 'Mažai vartojantis'],
                'user_friendly'    => ['Lengvai naudojamas', 'Intuityvus', 'Pradedantiesiems'],
                'durable'          => ['Ilgaamžis', 'Patvarus', 'Patikimas'],
            ],
            'technical' => [
                'processor' => ['Greitas', 'Efektyvus', 'Didelio našumo'],
                'memory'    => ['Didelės talpos', 'Sparti', 'Išplečiama'],
                'storage'   => ['Talpi', 'Greitas perdavimas', 'Saugus'],
            ],
            'performance' => [
                'speed'      => ['Greitas', 'Ypač greitas', 'Žaibiškas'],
                'quality'    => ['Aukštos kokybės', 'Premium', 'Profesionalus'],
                'efficiency' => ['Optimizuotas', 'Supaprastintas', 'Patobulintas'],
            ],
        ],
        'clothing' => [
            'specification' => [
                'material' => ['Medvilnė', 'Poliesteris', 'Vilna', 'Šilkas', 'Linas'],
                'size'     => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'color'    => ['Juoda', 'Balta', 'Mėlyna', 'Raudona', 'Žalia', 'Įvairiaspalvė'],
                'fit'      => ['Aptemptas', 'Įprastas', 'Laisvas', 'Perdydis'],
            ],
            'benefit' => [
                'comfort'     => ['Patogus', 'Minkštas', 'Kvėpuojantis'],
                'style'       => ['Madingas', 'Stilingas', 'Klasikinis', 'Šiuolaikiškas'],
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
                'material'   => ['Medis', 'Metalas', 'Plastikas', 'Stiklas', 'Keramika'],
                'capacity'   => ['Maža', 'Vidutinė', 'Didelė', 'Ypač didelė'],
            ],
            'benefit' => [
                'durability'    => ['Ilgaamžiškas', 'Atsparus orams', 'Tvirtas'],
                'aesthetics'    => ['Gražus', 'Elegantiškas', 'Modernus', 'Tradicinis'],
                'functionality' => ['Praktiškas', 'Daugiafunkcis', 'Taupantis vietą'],
            ],
            'technical' => [
                'installation' => ['Lengvas montavimas', 'Reikia specialisto', 'Tinka DIY'],
                'maintenance'  => ['Mažai priežiūros', 'Lengvai valomas', 'Savaime išsivalantis'],
            ],
        ],
    ];

    private const GENERIC_FEATURES = [
        'warranty'     => ['1 metų garantija', '2 metų garantija', '3 metų garantija', '5 metų garantija', 'Viso gyvenimo garantija'],
        'shipping'     => ['Nemokamas pristatymas', 'Skubi pristatymo paslauga', 'Standartinis pristatymas'],
        'availability' => ['Sandėlyje', 'Ribotas kiekis', 'Išankstinis užsakymas'],
        'rating'       => ['5 žvaigždutės', 'Aukštai įvertintas', 'Klientų favoritas'],
        'popularity'   => ['Perkamiausias', 'Populiarus pasirinkimas', 'Tendencijas kuriantis'],
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
            $featureStates = self::generateUniqueFeatureStates($categoryKey, $featureCount);

            if ($featureStates === []) {
                continue;
            }

            ProductFeature::factory()
                ->count(count($featureStates))
                ->for($product)
                ->sequence(...$featureStates)
                ->create();
        }
    }

    /**
     * Generate weight based on feature type
     */
    private static function generateWeight(string $featureType): float
    {
        return match ($featureType) {
            'specification' => fake()->numberBetween(80, 100) / 100,
            'benefit'       => fake()->numberBetween(70, 95) / 100,
            'technical'     => fake()->numberBetween(60, 90) / 100,
            'performance'   => fake()->numberBetween(75, 100) / 100,
            default         => fake()->numberBetween(50, 85) / 100,
        };
    }

    /**
     * Generate a set of unique feature states for a product.
     */
    private static function generateUniqueFeatureStates(string $categoryKey, int $desiredCount): array
    {
        $states = [];
        $usedPairs = [];

        $categoryPairs = self::buildCategoryPairs($categoryKey);
        self::appendPairsToStates($states, $usedPairs, $categoryPairs, $desiredCount);

        if (count($states) < $desiredCount) {
            $genericPairs = self::buildGenericPairs();
            self::appendPairsToStates($states, $usedPairs, $genericPairs, $desiredCount);
        }

        return array_slice($states, 0, $desiredCount);
    }

    /**
     * Build feature pairs from the category templates.
     *
     * @return array<int, array{feature_type: string, feature_key: string, values: array<int, string>}>
     */
    private static function buildCategoryPairs(string $categoryKey): array
    {
        $pairs = [];
        $categoryFeatures = self::FEATURE_TEMPLATES[$categoryKey] ?? [];

        foreach ($categoryFeatures as $featureType => $featureOptions) {
            foreach ($featureOptions as $featureKey => $values) {
                $pairs[] = [
                    'feature_type' => $featureType,
                    'feature_key'  => $featureKey,
                    'values'       => $values,
                ];
            }
        }

        shuffle($pairs);

        return $pairs;
    }

    /**
     * Build generic feature pairs using all available feature types.
     *
     * @return array<int, array{feature_type: string, feature_key: string, values: array<int, string>}>
     */
    private static function buildGenericPairs(): array
    {
        $pairs = [];

        foreach (array_keys(self::GENERIC_FEATURES) as $featureKey) {
            foreach (self::FEATURE_TYPES as $featureType) {
                $pairs[] = [
                    'feature_type' => $featureType,
                    'feature_key'  => $featureKey,
                    'values'       => self::GENERIC_FEATURES[$featureKey],
                ];
            }
        }

        shuffle($pairs);

        return $pairs;
    }

    /**
     * Append feature pairs to the state list while ensuring uniqueness.
     *
     * @param array<int, array{feature_type: string, feature_key: string, feature_value: string, weight: float}> $states
     * @param array<string, bool>                                                                                $usedPairs
     * @param array<int, array{feature_type: string, feature_key: string, values: array<int, string>}>           $pairs
     */
    private static function appendPairsToStates(array &$states, array &$usedPairs, array $pairs, int $desiredCount): void
    {
        foreach ($pairs as $pair) {
            if (count($states) >= $desiredCount) {
                break;
            }

            $signature = $pair['feature_type'] . '|' . $pair['feature_key'];

            if (isset($usedPairs[$signature])) {
                continue;
            }

            $states[] = [
                'feature_type'  => $pair['feature_type'],
                'feature_key'   => $pair['feature_key'],
                'feature_value' => Arr::random($pair['values']),
                'weight'        => self::generateWeight($pair['feature_type']),
            ];

            $usedPairs[$signature] = true;
        }
    }
}
