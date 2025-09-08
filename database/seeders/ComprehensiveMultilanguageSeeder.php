<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CountryTranslation;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Product;
use App\Models\Zone;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ComprehensiveMultilanguageSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;
    private array $supportedLocales = ['lt', 'en'];

    public function __construct()
    {
        $this->imageGenerator = app(LocalImageGeneratorService::class);
    }

    public function run(): void
    {
        $this->command->info('🌍 Starting comprehensive multilanguage seeding...');

        DB::transaction(function () {
            $this->seedCountriesWithTranslations();
            $this->seedZonesWithTranslations();
            $this->seedCurrenciesWithTranslations();
            $this->seedLocationsWithTranslations();
            $this->seedCategoriesWithTranslations();
            $this->seedBrandsWithTranslations();
            $this->seedCollectionsWithTranslations();
            $this->seedProductsWithTranslations();
        });

        $this->command->info('✅ Comprehensive multilanguage seeding completed!');
    }

    private function seedCountriesWithTranslations(): void
    {
        $this->command->info('🏳️ Seeding countries with translations...');

        $countries = [
            [
                'cca2' => 'LT', 'cca3' => 'LTU', 'phone_calling_code' => '370', 'flag' => '🇱🇹',
                'region' => 'Europe', 'subregion' => 'Northern Europe',
                'latitude' => 55.169438, 'longitude' => 23.881275, 'currencies' => ['EUR'],
                'translations' => [
                    'lt' => ['name' => 'Lietuva', 'name_official' => 'Lietuvos Respublika'],
                    'en' => ['name' => 'Lithuania', 'name_official' => 'Republic of Lithuania'],
                ]
            ],
            [
                'cca2' => 'LV', 'cca3' => 'LVA', 'phone_calling_code' => '371', 'flag' => '🇱🇻',
                'region' => 'Europe', 'subregion' => 'Northern Europe',
                'latitude' => 56.879635, 'longitude' => 24.603189, 'currencies' => ['EUR'],
                'translations' => [
                    'lt' => ['name' => 'Latvija', 'name_official' => 'Latvijos Respublika'],
                    'en' => ['name' => 'Latvia', 'name_official' => 'Republic of Latvia'],
                ]
            ],
            [
                'cca2' => 'EE', 'cca3' => 'EST', 'phone_calling_code' => '372', 'flag' => '🇪🇪',
                'region' => 'Europe', 'subregion' => 'Northern Europe',
                'latitude' => 58.595272, 'longitude' => 25.013607, 'currencies' => ['EUR'],
                'translations' => [
                    'lt' => ['name' => 'Estija', 'name_official' => 'Estijos Respublika'],
                    'en' => ['name' => 'Estonia', 'name_official' => 'Republic of Estonia'],
                ]
            ],
            [
                'cca2' => 'DE', 'cca3' => 'DEU', 'phone_calling_code' => '49', 'flag' => '🇩🇪',
                'region' => 'Europe', 'subregion' => 'Central Europe',
                'latitude' => 51.165691, 'longitude' => 10.451526, 'currencies' => ['EUR'],
                'translations' => [
                    'lt' => ['name' => 'Vokietija', 'name_official' => 'Vokietijos Federacinė Respublika'],
                    'en' => ['name' => 'Germany', 'name_official' => 'Federal Republic of Germany'],
                ]
            ],
            [
                'cca2' => 'PL', 'cca3' => 'POL', 'phone_calling_code' => '48', 'flag' => '🇵🇱',
                'region' => 'Europe', 'subregion' => 'Central Europe',
                'latitude' => 51.919438, 'longitude' => 19.145136, 'currencies' => ['PLN'],
                'translations' => [
                    'lt' => ['name' => 'Lenkija', 'name_official' => 'Lenkijos Respublika'],
                    'en' => ['name' => 'Poland', 'name_official' => 'Republic of Poland'],
                ]
            ],
            [
                'cca2' => 'FR', 'cca3' => 'FRA', 'phone_calling_code' => '33', 'flag' => '🇫🇷',
                'region' => 'Europe', 'subregion' => 'Western Europe',
                'latitude' => 46.227638, 'longitude' => 2.213749, 'currencies' => ['EUR'],
                'translations' => [
                    'lt' => ['name' => 'Prancūzija', 'name_official' => 'Prancūzijos Respublika'],
                    'en' => ['name' => 'France', 'name_official' => 'French Republic'],
                ]
            ],
            [
                'cca2' => 'GB', 'cca3' => 'GBR', 'phone_calling_code' => '44', 'flag' => '🇬🇧',
                'region' => 'Europe', 'subregion' => 'Northern Europe',
                'latitude' => 55.378051, 'longitude' => -3.435973, 'currencies' => ['GBP'],
                'translations' => [
                    'lt' => ['name' => 'Jungtinė Karalystė', 'name_official' => 'Didžiosios Britanijos ir Šiaurės Airijos Jungtinė Karalystė'],
                    'en' => ['name' => 'United Kingdom', 'name_official' => 'United Kingdom of Great Britain and Northern Ireland'],
                ]
            ],
            [
                'cca2' => 'US', 'cca3' => 'USA', 'phone_calling_code' => '1', 'flag' => '🇺🇸',
                'region' => 'Americas', 'subregion' => 'North America',
                'latitude' => 37.09024, 'longitude' => -95.712891, 'currencies' => ['USD'],
                'translations' => [
                    'lt' => ['name' => 'Jungtinės Amerikos Valstijos', 'name_official' => 'Amerikos Jungtinės Valstijos'],
                    'en' => ['name' => 'United States', 'name_official' => 'United States of America'],
                ]
            ],
        ];

        foreach ($countries as $countryData) {
            $country = Country::updateOrCreate(
                ['cca2' => $countryData['cca2']],
                [
                    'cca3' => $countryData['cca3'],
                    'phone_calling_code' => $countryData['phone_calling_code'],
                    'flag' => $countryData['flag'],
                    'region' => $countryData['region'],
                    'subregion' => $countryData['subregion'],
                    'latitude' => $countryData['latitude'],
                    'longitude' => $countryData['longitude'],
                    'currencies' => $countryData['currencies'],
                    'is_enabled' => true,
                    'sort_order' => array_search($countryData['cca2'], array_column($countries, 'cca2')) + 1,
                ]
            );

            foreach ($countryData['translations'] as $locale => $translation) {
                CountryTranslation::updateOrCreate(
                    ['country_id' => $country->id, 'locale' => $locale],
                    $translation
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($countries) . ' countries with translations');
    }

    private function seedZonesWithTranslations(): void
    {
        $this->command->info('🌍 Seeding zones with translations...');

        $zones = [
            [
                'name' => 'Europe',
                'slug' => 'europe',
                'code' => 'EU',
                'is_enabled' => true,
                'translations' => [
                    'lt' => ['name' => 'Europa', 'description' => 'Europos regionas'],
                    'en' => ['name' => 'Europe', 'description' => 'European region'],
                ]
            ],
            [
                'name' => 'North America',
                'slug' => 'north-america',
                'code' => 'NA',
                'is_enabled' => true,
                'translations' => [
                    'lt' => ['name' => 'Šiaurės Amerika', 'description' => 'Šiaurės Amerikos regionas'],
                    'en' => ['name' => 'North America', 'description' => 'North American region'],
                ]
            ],
            [
                'name' => 'Baltic States',
                'slug' => 'baltic-states',
                'code' => 'BALTIC',
                'is_enabled' => true,
                'translations' => [
                    'lt' => ['name' => 'Baltijos šalys', 'description' => 'Lietuvos, Latvijos ir Estijos regionas'],
                    'en' => ['name' => 'Baltic States', 'description' => 'Lithuania, Latvia and Estonia region'],
                ]
            ],
        ];

        foreach ($zones as $zoneData) {
            $zone = Zone::updateOrCreate(
                ['code' => $zoneData['code']],
                [
                    'name' => $zoneData['name'],
                    'slug' => Str::slug($zoneData['name']),
                    'is_enabled' => $zoneData['is_enabled'],
                ]
            );

            foreach ($zoneData['translations'] as $locale => $translation) {
                DB::table('zone_translations')->updateOrInsert(
                    ['zone_id' => $zone->id, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($zones) . ' zones with translations');
    }

    private function seedCurrenciesWithTranslations(): void
    {
        $this->command->info('💰 Seeding currencies with translations...');

        $currencies = [
            [
                'code' => 'EUR',
                'symbol' => '€',
                'name' => 'Euro',
                'exchange_rate' => 1.0,
                'is_default' => true,
                'translations' => [
                    'lt' => ['name' => 'Euras'],
                    'en' => ['name' => 'Euro'],
                ]
            ],
            [
                'code' => 'USD',
                'symbol' => '$',
                'name' => 'US Dollar',
                'exchange_rate' => 1.08,
                'is_default' => false,
                'translations' => [
                    'lt' => ['name' => 'JAV doleris'],
                    'en' => ['name' => 'US Dollar'],
                ]
            ],
            [
                'code' => 'GBP',
                'symbol' => '£',
                'name' => 'British Pound',
                'exchange_rate' => 0.85,
                'is_default' => false,
                'translations' => [
                    'lt' => ['name' => 'Didžiosios Britanijos svaras'],
                    'en' => ['name' => 'British Pound'],
                ]
            ],
        ];

        foreach ($currencies as $currencyData) {
            $currency = Currency::updateOrCreate(
                ['code' => $currencyData['code']],
                [
                    'symbol' => $currencyData['symbol'],
                    'name' => $currencyData['name'],
                    'exchange_rate' => $currencyData['exchange_rate'],
                    'is_default' => $currencyData['is_default'],
                    'is_enabled' => true,
                ]
            );

            foreach ($currencyData['translations'] as $locale => $translation) {
                DB::table('currency_translations')->updateOrInsert(
                    ['currency_id' => $currency->id, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($currencies) . ' currencies with translations');
    }

    private function seedLocationsWithTranslations(): void
    {
        $this->command->info('📍 Seeding locations with translations...');

        $locations = [
            [
                'code' => 'VLN001',
                'address_line_1' => 'Gedimino pr. 9',
                'city' => 'Vilnius',
                'state' => 'Vilnius County',
                'postal_code' => '01103',
                'country_code' => 'LT',
                'phone' => '+370 5 123 4567',
                'email' => 'vilnius@statybaecommerse.lt',
                'is_enabled' => true,
                'is_default' => true,
                'type' => 'warehouse',
                'translations' => [
                    'lt' => [
                        'name' => 'Vilniaus sandėlis',
                        'slug' => 'vilniaus-sandelis',
                        'description' => 'Pagrindinis sandėlis Vilniuje su pilnu statybos medžiagų asortimentu',
                    ],
                    'en' => [
                        'name' => 'Vilnius Warehouse',
                        'slug' => 'vilnius-warehouse',
                        'description' => 'Main warehouse in Vilnius with full range of construction materials',
                    ],
                ]
            ],
            [
                'code' => 'KNS001',
                'address_line_1' => 'Laisvės al. 53',
                'city' => 'Kaunas',
                'state' => 'Kaunas County',
                'postal_code' => '44309',
                'country_code' => 'LT',
                'phone' => '+370 37 123 456',
                'email' => 'kaunas@statybaecommerse.lt',
                'is_enabled' => true,
                'is_default' => false,
                'type' => 'store',
                'translations' => [
                    'lt' => [
                        'name' => 'Kauno parduotuvė',
                        'slug' => 'kauno-parduotuve',
                        'description' => 'Parduotuvė Kaune su statybos medžiagomis ir įrankiais',
                    ],
                    'en' => [
                        'name' => 'Kaunas Store',
                        'slug' => 'kaunas-store',
                        'description' => 'Store in Kaunas with construction materials and tools',
                    ],
                ]
            ],
            [
                'code' => 'KLP001',
                'address_line_1' => 'Taikos pr. 61',
                'city' => 'Klaipėda',
                'state' => 'Klaipėda County',
                'postal_code' => '91181',
                'country_code' => 'LT',
                'phone' => '+370 46 123 789',
                'email' => 'klaipeda@statybaecommerse.lt',
                'is_enabled' => true,
                'is_default' => false,
                'type' => 'pickup_point',
                'translations' => [
                    'lt' => [
                        'name' => 'Klaipėdos atsiėmimo punktas',
                        'slug' => 'klaipedos-atsemimo-punktas',
                        'description' => 'Patogus atsiėmimo punktas Klaipėdoje',
                    ],
                    'en' => [
                        'name' => 'Klaipėda Pickup Point',
                        'slug' => 'klaipeda-pickup-point',
                        'description' => 'Convenient pickup point in Klaipėda',
                    ],
                ]
            ],
        ];

        foreach ($locations as $locationData) {
            $translations = $locationData['translations'];
            unset($locationData['translations']);

            $location = Location::updateOrCreate(
                ['code' => $locationData['code']],
                array_merge($locationData, [
                    'name' => collect($translations)->mapWithKeys(fn($trans, $locale) => [$locale => $trans['name']])->all(),
                    'slug' => collect($translations)->mapWithKeys(fn($trans, $locale) => [$locale => $trans['slug']])->all(),
                    'description' => collect($translations)->mapWithKeys(fn($trans, $locale) => [$locale => $trans['description']])->all(),
                ])
            );

            $this->command->info("   📍 Created location: {$location->getTranslation('name', 'en')}");
        }

        $this->command->info('   ✅ Created ' . count($locations) . ' locations with translations');
    }

    private function seedCategoriesWithTranslations(): void
    {
        $this->command->info('📂 Seeding categories with translations and images...');

        $categories = [
            [
                'name' => 'Construction Materials',
                'slug' => 'construction-materials',
                'description' => 'High-quality construction materials for all your building needs',
                'is_enabled' => true,
                'sort_order' => 1,
                'translations' => [
                    'lt' => [
                        'name' => 'Statybos medžiagos',
                        'slug' => 'statybos-medziagos',
                        'description' => 'Aukštos kokybės statybos medžiagos visiems jūsų statybos poreikiams',
                    ],
                    'en' => [
                        'name' => 'Construction Materials',
                        'slug' => 'construction-materials',
                        'description' => 'High-quality construction materials for all your building needs',
                    ],
                ]
            ],
            [
                'name' => 'Tools & Equipment',
                'slug' => 'tools-equipment',
                'description' => 'Professional tools and equipment for construction and renovation',
                'is_enabled' => true,
                'sort_order' => 2,
                'translations' => [
                    'lt' => [
                        'name' => 'Įrankiai ir įranga',
                        'slug' => 'irankiai-iranga',
                        'description' => 'Profesionalūs įrankiai ir įranga statybai ir remontui',
                    ],
                    'en' => [
                        'name' => 'Tools & Equipment',
                        'slug' => 'tools-equipment',
                        'description' => 'Professional tools and equipment for construction and renovation',
                    ],
                ]
            ],
            [
                'name' => 'Electrical Supplies',
                'slug' => 'electrical-supplies',
                'description' => 'Complete range of electrical supplies and components',
                'is_enabled' => true,
                'sort_order' => 3,
                'translations' => [
                    'lt' => [
                        'name' => 'Elektros prekės',
                        'slug' => 'elektros-prekes',
                        'description' => 'Pilnas elektros prekių ir komponentų asortimentas',
                    ],
                    'en' => [
                        'name' => 'Electrical Supplies',
                        'slug' => 'electrical-supplies',
                        'description' => 'Complete range of electrical supplies and components',
                    ],
                ]
            ],
            [
                'name' => 'Plumbing & Heating',
                'slug' => 'plumbing-heating',
                'description' => 'Plumbing fixtures, pipes, and heating systems',
                'is_enabled' => true,
                'sort_order' => 4,
                'translations' => [
                    'lt' => [
                        'name' => 'Santechnika ir šildymas',
                        'slug' => 'santechnika-sildymas',
                        'description' => 'Santechnikos įranga, vamzdžiai ir šildymo sistemos',
                    ],
                    'en' => [
                        'name' => 'Plumbing & Heating',
                        'slug' => 'plumbing-heating',
                        'description' => 'Plumbing fixtures, pipes, and heating systems',
                    ],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'is_visible' => $categoryData['is_enabled'],
                    'sort_order' => $categoryData['sort_order'],
                ]
            );

            // Generate category image if on localhost
            if (app()->environment('local')) {
                try {
                    $imagePath = $this->imageGenerator->generateCategoryImage($categoryData['name']);
                    if ($imagePath && file_exists($imagePath)) {
                        $category->clearMediaCollection('images');
                        $category
                            ->addMedia($imagePath)
                            ->withCustomProperties(['source' => 'local_generated'])
                            ->usingName($categoryData['name'] . ' Image')
                            ->toMediaCollection('images');

                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to generate category image', [
                        'category' => $categoryData['name'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            foreach ($categoryData['translations'] as $locale => $translation) {
                DB::table('category_translations')->updateOrInsert(
                    ['category_id' => $category->id, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($categories) . ' categories with translations and images');
    }

    private function seedBrandsWithTranslations(): void
    {
        $this->command->info('🏷️ Seeding brands with translations and images...');

        $brands = [
            [
                'name' => 'BuildPro',
                'slug' => 'buildpro',
                'description' => 'Professional construction materials and tools',
                'is_enabled' => true,
                'sort_order' => 1,
                'translations' => [
                    'lt' => [
                        'name' => 'BuildPro',
                        'slug' => 'buildpro',
                        'description' => 'Profesionalios statybos medžiagos ir įrankiai',
                    ],
                    'en' => [
                        'name' => 'BuildPro',
                        'slug' => 'buildpro',
                        'description' => 'Professional construction materials and tools',
                    ],
                ]
            ],
            [
                'name' => 'ElectroMax',
                'slug' => 'electromax',
                'description' => 'Leading electrical supplies manufacturer',
                'is_enabled' => true,
                'sort_order' => 2,
                'translations' => [
                    'lt' => [
                        'name' => 'ElectroMax',
                        'slug' => 'electromax',
                        'description' => 'Pirmaujantis elektros prekių gamintojas',
                    ],
                    'en' => [
                        'name' => 'ElectroMax',
                        'slug' => 'electromax',
                        'description' => 'Leading electrical supplies manufacturer',
                    ],
                ]
            ],
            [
                'name' => 'PlumbTech',
                'slug' => 'plumbtech',
                'description' => 'Advanced plumbing and heating solutions',
                'is_enabled' => true,
                'sort_order' => 3,
                'translations' => [
                    'lt' => [
                        'name' => 'PlumbTech',
                        'slug' => 'plumbtech',
                        'description' => 'Pažangūs santechnikos ir šildymo sprendimai',
                    ],
                    'en' => [
                        'name' => 'PlumbTech',
                        'slug' => 'plumbtech',
                        'description' => 'Advanced plumbing and heating solutions',
                    ],
                ]
            ],
        ];

        foreach ($brands as $brandData) {
            $brand = Brand::updateOrCreate(
                ['slug' => $brandData['slug']],
                [
                    'name' => $brandData['name'],
                    'description' => $brandData['description'],
                    'is_enabled' => $brandData['is_enabled'],
                    'sort_order' => $brandData['sort_order'],
                ]
            );

            // Generate brand logo if on localhost
            if (app()->environment('local')) {
                try {
                    $imagePath = $this->imageGenerator->generateBrandLogo($brandData['name']);
                    if ($imagePath && file_exists($imagePath)) {
                        $brand->clearMediaCollection('logo');
                        $brand
                            ->addMedia($imagePath)
                            ->withCustomProperties(['source' => 'local_generated'])
                            ->usingName($brandData['name'] . ' Logo')
                            ->toMediaCollection('logo');

                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to generate brand logo', [
                        'brand' => $brandData['name'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            foreach ($brandData['translations'] as $locale => $translation) {
                DB::table('brand_translations')->updateOrInsert(
                    ['brand_id' => $brand->id, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($brands) . ' brands with translations and images');
    }

    private function seedCollectionsWithTranslations(): void
    {
        $this->command->info('📦 Seeding collections with translations...');

        $collections = [
            [
                'name' => 'Professional Series',
                'slug' => 'professional-series',
                'description' => 'Premium products for professional contractors',
                'is_enabled' => true,
                'sort_order' => 1,
                'translations' => [
                    'lt' => [
                        'name' => 'Profesionalų serija',
                        'slug' => 'profesionalu-serija',
                        'description' => 'Aukščiausios kokybės produktai profesionaliems rangovams',
                    ],
                    'en' => [
                        'name' => 'Professional Series',
                        'slug' => 'professional-series',
                        'description' => 'Premium products for professional contractors',
                    ],
                ]
            ],
            [
                'name' => 'Home Builder',
                'slug' => 'home-builder',
                'description' => 'Everything you need for home construction and renovation',
                'is_enabled' => true,
                'sort_order' => 2,
                'translations' => [
                    'lt' => [
                        'name' => 'Namų statytojas',
                        'slug' => 'namu-statytojas',
                        'description' => 'Viskas, ko reikia namų statybai ir remontui',
                    ],
                    'en' => [
                        'name' => 'Home Builder',
                        'slug' => 'home-builder',
                        'description' => 'Everything you need for home construction and renovation',
                    ],
                ]
            ],
        ];

        foreach ($collections as $collectionData) {
            $collection = Collection::updateOrCreate(
                ['slug' => $collectionData['slug']],
                [
                    'name' => $collectionData['name'],
                    'description' => $collectionData['description'],
                    'is_enabled' => $collectionData['is_enabled'],
                    'sort_order' => $collectionData['sort_order'],
                ]
            );

            foreach ($collectionData['translations'] as $locale => $translation) {
                DB::table('collection_translations')->updateOrInsert(
                    ['collection_id' => $collection->id, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($collections) . ' collections with translations');
    }

    private function seedProductsWithTranslations(): void
    {
        $this->command->info('🛍️ Seeding products with translations and images...');

        $categories = Category::all();
        $brands = Brand::all();

        if ($categories->isEmpty() || $brands->isEmpty()) {
            $this->command->warn('Categories or brands not found. Skipping product seeding.');
            return;
        }

        $products = [
            [
                'name' => 'Premium Cement 50kg',
                'slug' => 'premium-cement-50kg',
                'sku' => 'CEM-PREM-50',
                'summary' => 'High-quality Portland cement for construction',
                'description' => 'Premium Portland cement suitable for all construction projects. Provides excellent strength and durability.',
                'price' => 12.99,
                'compare_price' => 15.99,
                'cost_price' => 8.5,
                'is_enabled' => true,
                'translations' => [
                    'lt' => [
                        'name' => 'Aukščiausios kokybės cementas 50kg',
                        'slug' => 'auksciausia-kokybes-cementas-50kg',
                        'summary' => 'Aukštos kokybės Portlando cementas statybai',
                        'description' => 'Aukščiausios kokybės Portlando cementas, tinkamas visiems statybos projektams. Užtikrina puikų stiprumą ir ilgaamžiškumą.',
                    ],
                    'en' => [
                        'name' => 'Premium Cement 50kg',
                        'slug' => 'premium-cement-50kg',
                        'summary' => 'High-quality Portland cement for construction',
                        'description' => 'Premium Portland cement suitable for all construction projects. Provides excellent strength and durability.',
                    ],
                ]
            ],
            [
                'name' => 'Professional Drill Set',
                'slug' => 'professional-drill-set',
                'sku' => 'DRILL-PRO-SET',
                'summary' => 'Complete professional drill set with accessories',
                'description' => 'Professional-grade drill set including drill bits, screwdriver bits, and carrying case.',
                'price' => 89.99,
                'compare_price' => 109.99,
                'cost_price' => 55.0,
                'is_enabled' => true,
                'translations' => [
                    'lt' => [
                        'name' => 'Profesionalus grąžtų rinkinys',
                        'slug' => 'profesionalus-graztu-rinkinys',
                        'summary' => 'Pilnas profesionalus grąžtų rinkinys su priedais',
                        'description' => 'Profesionalios klasės grąžtų rinkinys su grąžtais, atsuktuvo antgaliais ir nešimo dėklu.',
                    ],
                    'en' => [
                        'name' => 'Professional Drill Set',
                        'slug' => 'professional-drill-set',
                        'summary' => 'Complete professional drill set with accessories',
                        'description' => 'Professional-grade drill set including drill bits, screwdriver bits, and carrying case.',
                    ],
                ]
            ],
            [
                'name' => 'LED Light Bulb 10W',
                'slug' => 'led-light-bulb-10w',
                'sku' => 'LED-BULB-10W',
                'summary' => 'Energy-efficient LED light bulb',
                'description' => 'High-efficiency LED bulb with warm white light. Long-lasting and energy-saving.',
                'price' => 4.99,
                'compare_price' => 6.99,
                'cost_price' => 2.5,
                'is_enabled' => true,
                'translations' => [
                    'lt' => [
                        'name' => 'LED lemputė 10W',
                        'slug' => 'led-lempute-10w',
                        'summary' => 'Energiją taupanti LED lemputė',
                        'description' => 'Aukšto efektyvumo LED lemputė su šilta balta šviesa. Ilgaamžė ir energiją taupanti.',
                    ],
                    'en' => [
                        'name' => 'LED Light Bulb 10W',
                        'slug' => 'led-light-bulb-10w',
                        'summary' => 'Energy-efficient LED light bulb',
                        'description' => 'High-efficiency LED bulb with warm white light. Long-lasting and energy-saving.',
                    ],
                ]
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'name' => $productData['name'],
                    'slug' => $productData['slug'],
                    'summary' => $productData['summary'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'compare_price' => $productData['compare_price'],
                    'cost_price' => $productData['cost_price'],
                    'is_enabled' => $productData['is_enabled'],
                    'is_visible' => $productData['is_enabled'],
                    'brand_id' => $brands->random()->id,
                ]
            );

            // Attach random categories to the product
            $randomCategories = $categories->random(rand(1, 2));
            $product->categories()->sync($randomCategories->pluck('id')->toArray());

            // Generate product images if on localhost
            if (app()->environment('local')) {
                try {
                    $imagePath = $this->imageGenerator->generateProductImage(
                        $productData['name'],
                        $randomCategories->first()->name ?? 'general'
                    );

                    if ($imagePath && file_exists($imagePath)) {
                        $product->clearMediaCollection('images');
                        $product
                            ->addMedia($imagePath)
                            ->withCustomProperties(['source' => 'local_generated'])
                            ->usingName($productData['name'] . ' Image')
                            ->toMediaCollection('images');

                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to generate product image', [
                        'product' => $productData['name'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            foreach ($productData['translations'] as $locale => $translation) {
                DB::table('product_translations')->updateOrInsert(
                    ['product_id' => $product->id, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $this->command->info('   ✅ Created ' . count($products) . ' products with translations and images');
    }
}
