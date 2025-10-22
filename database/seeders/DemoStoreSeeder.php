<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Translations\CategoryTranslation;
use App\Models\Translations\ProductTranslation;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class DemoStoreSeeder extends Seeder
{
    private const LOCALES = ['en', 'lt', 'lv'];
    private const DEFAULT_LOCALE = 'en';

    public function run(): void
    {
        DB::transaction(function (): void {
            $country = $this->seedCountry();
            $channel = $this->seedChannel();
            $zone = $this->seedZone();

            $brands = $this->seedBrands();
            $categories = $this->seedCategories();
            $catalog = $this->seedProducts($brands, $categories);
            $customers = $this->seedCustomers();

            $this->seedOrders($customers, $catalog, $channel, $zone, $country);
        });
    }

    private function seedCountry(): Country
    {
        $attributes = Country::factory()->lithuania()->raw();

        // Normalise translated fields so the base table stores the default locale while the
        // remaining translations are persisted in the country_translations relation.
        $translatedFields = ['name', 'name_official', 'description'];
        $translationsByLocale = [];

        foreach ($translatedFields as $field) {
            if (! array_key_exists($field, $attributes)) {
                continue;
            }

            $value = $attributes[$field];

            if (! is_array($value)) {
                continue;
            }

            foreach ($value as $locale => $translatedValue) {
                $translationsByLocale[$locale][$field] = $translatedValue;
            }

            $attributes[$field] = $value[self::DEFAULT_LOCALE] ?? reset($value) ?? null;
        }

        $country = Country::query()->updateOrCreate(
            ['cca2' => $attributes['cca2']],
            $attributes,
        );

        foreach ($translationsByLocale as $locale => $payload) {
            // Ensure each locale is synchronised without overwriting unrelated translation
            // fields, keeping the demo content resilient to repeated seed executions.
            $country->translations()->updateOrCreate(
                ['locale' => $locale],
                $payload,
            );
        }

        return $country;
    }

    private function seedChannel(): Channel
    {
        $attributes = Channel::factory()->web()->raw();

        return Channel::query()->updateOrCreate(
            ['slug' => $attributes['slug']],
            $attributes,
        );
    }

    private function seedZone(): Zone
    {
        $attributes = Zone::factory()->lithuania()->raw();

        return Zone::query()->updateOrCreate(
            ['code' => $attributes['code']],
            $attributes,
        );
    }

    /**
     * @return array<string, Brand>
     */
    private function seedBrands(): array
    {
        $definitions = [
            'makita' => [
                'name' => 'Makita',
                'slug' => 'makita',
                'description' => 'Japanese power tool innovator trusted on Baltic job sites.',
                'website' => 'https://www.makita.lt',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'Makita Professional Power Tools',
                'seo_description' => 'Makita cordless and corded tools engineered for construction crews in the Baltics.',
            ],
            'bosch' => [
                'name' => 'Bosch Professional',
                'slug' => 'bosch-professional',
                'description' => 'Bosch Pro range delivers durable tools and accessories for demanding teams.',
                'website' => 'https://www.bosch-professional.com/lt/lt/',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'Bosch Professional Tools',
                'seo_description' => 'Discover Bosch rotary hammers, saws, and lighting for Baltic contractors.',
            ],
            'dewalt' => [
                'name' => 'DeWalt',
                'slug' => 'dewalt',
                'description' => 'DeWalt delivers rugged jobsite tools for concrete, carpentry, and finishing trades.',
                'website' => 'https://www.dewalt.eu/lt-lt',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'DeWalt Jobsite Solutions',
                'seo_description' => 'Explore DeWalt XR tools, kits, and safety gear built for the Baltics.',
            ],
            'hilti' => [
                'name' => 'Hilti',
                'slug' => 'hilti',
                'description' => 'Hilti Nuron cordless solutions that excel in concrete and installation work.',
                'website' => 'https://www.hilti.lt',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'Hilti Nuron Systems',
                'seo_description' => 'Hilti rotary hammers, lasers, and fastening systems trusted by Baltic pros.',
            ],
            'milwaukee' => [
                'name' => 'Milwaukee',
                'slug' => 'milwaukee',
                'description' => 'Milwaukee M18 and MX FUEL solutions for mechanical, electrical, and plumbing teams.',
                'website' => 'https://www.milwaukeetool.eu/lt-lt',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'Milwaukee M18 & MX FUEL',
                'seo_description' => 'Powerful Milwaukee cordless systems with jobsite-tuned safety gear and lighting.',
            ],
            'metabo' => [
                'name' => 'Metabo',
                'slug' => 'metabo',
                'description' => 'Metabo grinders, saws, and extraction solutions crafted in Germany.',
                'website' => 'https://www.metabo.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Metabo Industrial Power Tools',
                'seo_description' => 'Reliable Metabo tools with long service life for workshops and construction sites.',
            ],
            'festool' => [
                'name' => 'Festool',
                'slug' => 'festool',
                'description' => 'Festool precision carpentry tools and dust extraction systems.',
                'website' => 'https://www.festool.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Festool Precision Woodworking',
                'seo_description' => 'Festool saws, sanders, and systainer sets for premium finishing work.',
            ],
            'ryobi' => [
                'name' => 'Ryobi',
                'slug' => 'ryobi',
                'description' => 'Ryobi ONE+ DIY and light professional cordless systems for Baltic homes.',
                'website' => 'https://www.ryobitools.eu',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Ryobi ONE+ Platform',
                'seo_description' => 'Affordable Ryobi kits and garden tools compatible with the ONE+ battery platform.',
            ],
            'black-decker' => [
                'name' => 'Black+Decker',
                'slug' => 'black-decker',
                'description' => 'Black+Decker kits cover essential drilling, cutting, and fastening tasks.',
                'website' => 'https://www.blackanddecker.eu',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Black+Decker Tool Kits',
                'seo_description' => 'Practical Black+Decker kits for renovation crews and homeowners.',
            ],
            'stanley' => [
                'name' => 'Stanley',
                'slug' => 'stanley',
                'description' => 'Stanley hand tools, lasers, and storage for installers and builders.',
                'website' => 'https://www.stanleytools.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Stanley Measuring & Hand Tools',
                'seo_description' => 'Trusted Stanley hand tools and laser levels for Baltic job sites.',
            ],
            'ridgid' => [
                'name' => 'RIDGID',
                'slug' => 'ridgid',
                'description' => 'RIDGID mechanic-grade tools and storage built for extreme environments.',
                'website' => 'https://www.ridgid.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'RIDGID Heavy-Duty Tools',
                'seo_description' => 'Socket sets and plumbing tools engineered for durability.',
            ],
            '3m' => [
                'name' => '3M',
                'slug' => '3m',
                'description' => '3M safety solutions delivering certified respiratory and head protection.',
                'website' => 'https://www.3m.com',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => '3M Safety Gear',
                'seo_description' => '3M helmets and respirators that meet EU safety requirements.',
            ],
            'uvex' => [
                'name' => 'uvex',
                'slug' => 'uvex',
                'description' => 'uvex head-to-toe personal protective equipment for industrial crews.',
                'website' => 'https://www.uvex-safety.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'uvex Personal Protective Equipment',
                'seo_description' => 'Premium uvex helmets, boots, and eyewear for Baltic worksites.',
            ],
            'honeywell' => [
                'name' => 'Honeywell Safety',
                'slug' => 'honeywell-safety',
                'description' => 'Honeywell personal protective equipment with reliable certification.',
                'website' => 'https://sps.honeywell.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Honeywell Safety Solutions',
                'seo_description' => 'Honeywell respirators, helmets, and boots for compliant teams.',
            ],
            'philips' => [
                'name' => 'Philips Lighting',
                'slug' => 'philips-lighting',
                'description' => 'Philips LED lighting and smart controls for industrial and site use.',
                'website' => 'https://www.signify.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Philips LED Lighting',
                'seo_description' => 'Energy-efficient Philips lighting and power distribution.',
            ],
            'osram' => [
                'name' => 'OSRAM',
                'slug' => 'osram',
                'description' => 'OSRAM work lights and electrical distribution products.',
                'website' => 'https://www.osram.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'OSRAM Work Lighting',
                'seo_description' => 'Durable OSRAM lights for night shifts and enclosed sites.',
            ],
            'legrand' => [
                'name' => 'Legrand',
                'slug' => 'legrand',
                'description' => 'Legrand cable management, extension systems, and smart controls.',
                'website' => 'https://www.legrand.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Legrand Power Distribution',
                'seo_description' => 'Extension cords, smart switches, and protection devices by Legrand.',
            ],
            'karcher' => [
                'name' => 'Kärcher',
                'slug' => 'karcher',
                'description' => 'Kärcher pressure washers and cleaning systems built for professionals.',
                'website' => 'https://www.kaercher.com',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'Kärcher Professional Cleaning',
                'seo_description' => 'High-pressure cleaners and accessories optimised for Baltic conditions.',
            ],
            'nilfisk' => [
                'name' => 'Nilfisk',
                'slug' => 'nilfisk',
                'description' => 'Nilfisk industrial cleaning and pressure washing technology.',
                'website' => 'https://www.nilfisk.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Nilfisk Pressure Washers',
                'seo_description' => 'Heavy-duty Nilfisk washers for vehicle fleets and equipment yards.',
            ],
            'stihl' => [
                'name' => 'STIHL',
                'slug' => 'stihl',
                'description' => 'STIHL outdoor power equipment trusted by landscaping crews.',
                'website' => 'https://www.stihl.com',
                'is_enabled' => true,
                'is_featured' => true,
                'seo_title' => 'STIHL Outdoor Power Tools',
                'seo_description' => 'Trimmers and chainsaws engineered for Baltic weather.',
            ],
            'husqvarna' => [
                'name' => 'Husqvarna',
                'slug' => 'husqvarna',
                'description' => 'Husqvarna landscaping machines and forestry solutions.',
                'website' => 'https://www.husqvarna.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Husqvarna Landscaping Equipment',
                'seo_description' => 'Line trimmers and blowers for municipal and commercial users.',
            ],
            'einhell' => [
                'name' => 'Einhell',
                'slug' => 'einhell',
                'description' => 'Einhell garden tools and light-construction equipment.',
                'website' => 'https://www.einhell.com',
                'is_enabled' => true,
                'is_featured' => false,
                'seo_title' => 'Einhell Garden Tools',
                'seo_description' => 'Cost-effective Einhell trimmers and washers for daily maintenance.',
            ],
        ];

        $brands = [];

        foreach ($definitions as $slug => $data) {
            $brands[$slug] = Brand::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }

        return $brands;
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $map = [];

        foreach ($this->categoryDefinitions() as $key => $definition) {
            $map += $this->storeCategory($key, $definition);
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, Category>
     */
    private function storeCategory(string $key, array $definition, ?Category $parent = null): array
    {
        $slug = $definition['slug'] ?? Str::slug(Str::afterLast($key, '.'));
        $translations = $definition['translations'] ?? [];

        if (! isset($translations[self::DEFAULT_LOCALE])) {
            throw new RuntimeException("Missing default translation for category {$key}");
        }

        $defaultTranslation = $translations[self::DEFAULT_LOCALE];

        $category = Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $defaultTranslation['name'],
                'slug' => $slug,
                'description' => $defaultTranslation['description'],
                'parent_id' => $parent?->getKey(),
                'sort_order' => $definition['sort_order'] ?? 0,
                'is_visible' => $definition['is_visible'] ?? true,
                'is_enabled' => true,
                'seo_title' => $defaultTranslation['seo_title'],
                'seo_description' => $defaultTranslation['seo_description'],
                'show_in_menu' => $definition['show_in_menu'] ?? $parent === null,
                'product_limit' => $definition['product_limit'] ?? null,
            ],
        );

        $this->syncCategoryTranslations($category, $translations);

        $map = [$key => $category];

        if (! empty($definition['children'])) {
            foreach ($definition['children'] as $childKey => $childDefinition) {
                $map += $this->storeCategory($childKey, $childDefinition, $category);
            }
        }

        return $map;
    }

    private function syncCategoryTranslations(Category $category, array $translations): void
    {
        foreach (self::LOCALES as $locale) {
            if (! isset($translations[$locale])) {
                throw new RuntimeException("Missing {$locale} translation for category {$category->slug}");
            }

            $data = $translations[$locale];

            CategoryTranslation::query()->updateOrCreate(
                [
                    'category_id' => $category->getKey(),
                    'locale' => $locale,
                ],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'],
                    'short_description' => $data['short_description'],
                    'seo_title' => $data['seo_title'],
                    'seo_description' => $data['seo_description'],
                    'seo_keywords' => $data['seo_keywords'],
                ],
            );
        }
    }

    /**
     * @param  array<string, Brand>  $brands
     * @param  array<string, Category>  $categories
     * @return array{featured: array<string, Product>, all: array<int, Product>}
     */
    private function seedProducts(array $brands, array $categories): array
    {
        $definitions = $this->buildProductDefinitions($brands, $categories);

        if (count($definitions) < 100) {
            throw new RuntimeException('Demo catalogue must expose at least 100 products.');
        }

        $allProducts = [];
        $featured = [];

        foreach ($definitions as $definition) {
            $brand = $brands[$definition['brand']];

            $product = Product::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'type' => 'simple',
                    'name' => $definition['translations'][self::DEFAULT_LOCALE]['name'],
                    'slug' => $definition['slug'],
                    'sku' => $definition['sku'],
                    'summary' => $definition['summary'],
                    'description' => $definition['description'],
                    'short_description' => $definition['short_description'],
                    'price' => $definition['price'],
                    'sale_price' => $definition['sale_price'],
                    'brand_id' => $brand->getKey(),
                    'stock_quantity' => $definition['stock_quantity'],
                    'low_stock_threshold' => $definition['low_stock_threshold'],
                    'manage_stock' => true,
                    'weight' => $definition['weight'],
                    'length' => $definition['length'],
                    'width' => $definition['width'],
                    'height' => $definition['height'],
                    'is_visible' => true,
                    'is_enabled' => true,
                    'is_featured' => $definition['is_featured'],
                    'status' => 'published',
                    'published_at' => $definition['published_at'],
                    'seo_title' => $definition['translations'][self::DEFAULT_LOCALE]['seo_title'],
                    'seo_description' => $definition['translations'][self::DEFAULT_LOCALE]['seo_description'],
                ],
            );

            $product->categories()->sync($definition['category_ids']);

            $this->syncProductTranslations($product, $definition['translations']);
            $this->syncProductImages($product, $definition['slug']);

            $allProducts[] = $product;

            if (! isset($featured[$definition['type_key']])) {
                $featured[$definition['type_key']] = $product;
            }
        }

        return [
            'featured' => [
                'rotaryHammer' => $featured['rotary-hammer'] ?? $allProducts[0],
                'comboKit' => $featured['combo-kit'] ?? $allProducts[1] ?? $allProducts[0],
                'safetyHelmet' => $featured['safety-helmet'] ?? $allProducts[2] ?? $allProducts[0],
            ],
            'all' => $allProducts,
        ];
    }

    /**
     * @param  array<string, Brand>  $brands
     * @param  array<string, Category>  $categories
     * @return array<int, array<string, mixed>>
     */
    private function buildProductDefinitions(array $brands, array $categories): array
    {
        $definitions = [];
        $skuCounters = [];
        $now = now();

        foreach ($this->productTypeConfigurations() as $typeKey => $configuration) {
            if (! isset($configuration['category_key'])) {
                throw new RuntimeException("Product type {$typeKey} is missing a category definition.");
            }

            $categoryKey = $configuration['category_key'];

            if (! isset($categories[$categoryKey])) {
                throw new RuntimeException("Category {$categoryKey} not seeded for product type {$typeKey}.");
            }

            $categoryIds = $this->resolveCategoryIds($categoryKey, $categories);
            $typeFeaturedAssigned = false;

            foreach ($configuration['models'] as $modelDefinition) {
                $brandKey = $modelDefinition['brand'];

                if (! isset($brands[$brandKey])) {
                    throw new RuntimeException("Brand {$brandKey} referenced by {$typeKey} product definitions was not seeded.");
                }

                $brand = $brands[$brandKey];
                $series = $modelDefinition['series'] ?? '';
                $seriesLabel = $series !== '' ? $series.' ' : '';
                $specification = $modelDefinition['spec'] ?? [];

                foreach ($modelDefinition['items'] as $itemDefinition) {
                    $skuCounters[$typeKey] = ($skuCounters[$typeKey] ?? 0) + 1;
                    $sequence = $skuCounters[$typeKey];
                    $sku = sprintf('%s-%03d', $configuration['sku_prefix'], $sequence);

                    $weight = $itemDefinition['weight_kg'] ?? $configuration['default_weight'];

                    $placeholders = array_merge(
                        $this->basePlaceholderDefaults(),
                        $specification,
                        Arr::except($itemDefinition, ['price', 'sale_price', 'weight_kg']),
                        [
                            'brand' => $brand->name,
                            'model' => $itemDefinition['model'],
                            'series' => $series,
                            'series_label' => $seriesLabel,
                            'weight' => number_format((float) $weight, 1, '.', '').' kg',
                            'weight_value' => $weight,
                        ],
                    );

                    $translations = [];

                    foreach (self::LOCALES as $locale) {
                        if (! isset($configuration['templates'][$locale])) {
                            throw new RuntimeException("Missing {$locale} templates for product type {$typeKey}.");
                        }

                        $placeholders['type_label'] = $configuration['type_label'][$locale] ?? $configuration['type_label'][self::DEFAULT_LOCALE];
                        $placeholders['type_display'] = $configuration['type_display'][$locale] ?? $configuration['type_display'][self::DEFAULT_LOCALE];

                        $templates = $configuration['templates'][$locale];

                        $name = $this->renderTemplate($templates['name'], $placeholders);
                        $summary = $this->renderTemplate($templates['summary'], $placeholders);
                        $shortDescription = $this->renderTemplate($templates['short_description'], $placeholders);
                        $description = $this->renderTemplate($templates['description'], $placeholders);
                        $seoTitle = $this->renderTemplate($templates['seo_title'], $placeholders);
                        $seoDescription = $this->renderTemplate($templates['seo_description'], $placeholders);

                        $translations[$locale] = [
                            'name' => $name,
                            'slug' => Str::slug($name),
                            'summary' => $summary,
                            'description' => $description,
                            'short_description' => $shortDescription,
                            'seo_title' => $seoTitle,
                            'seo_description' => $seoDescription,
                            'meta_keywords' => implode(', ', array_filter([
                                $brand->name,
                                $series,
                                $placeholders['type_label'],
                                $itemDefinition['model'],
                                $placeholders['power'] ?? null,
                                $placeholders['tools_included'] ?? null,
                            ])),
                            'alt_text' => $this->renderTemplate(match ($locale) {
                                'lt' => '{brand} {model} {type_display} iliustracija',
                                'lv' => '{brand} {model} {type_display} attēls',
                                default => '{brand} {model} {type_display} product photo',
                            }, $placeholders),
                        ];
                    }

                    $definitions[] = [
                        'type_key' => $typeKey,
                        'brand' => $brandKey,
                        'slug' => Str::slug($brand->slug.'-'.$itemDefinition['model'].'-'.$typeKey),
                        'sku' => $sku,
                        'summary' => $translations[self::DEFAULT_LOCALE]['summary'],
                        'description' => $translations[self::DEFAULT_LOCALE]['description'],
                        'short_description' => $translations[self::DEFAULT_LOCALE]['short_description'],
                        'price' => $itemDefinition['price'],
                        'sale_price' => $itemDefinition['sale_price'] ?? null,
                        'stock_quantity' => $configuration['stock']['base'] + (($sequence - 1) % 4) * $configuration['stock']['step'],
                        'low_stock_threshold' => max(2, (int) floor($configuration['stock']['step'] / 2) + 1),
                        'weight' => $weight,
                        'length' => $configuration['dimensions']['length'],
                        'width' => $configuration['dimensions']['width'],
                        'height' => $configuration['dimensions']['height'],
                        'is_featured' => ! $typeFeaturedAssigned,
                        'published_at' => $now->copy()->subDays($sequence + count($definitions) % 30),
                        'category_ids' => $categoryIds,
                        'translations' => $translations,
                    ];

                    $typeFeaturedAssigned = true;
                }
            }
        }

        return $definitions;
    }

    /**
     * @param  array<string, Category>  $categories
     * @return array<int, int>
     */
    private function resolveCategoryIds(string $categoryKey, array $categories): array
    {
        $ids = [];
        $current = $categoryKey;

        while (isset($categories[$current])) {
            $ids[] = $categories[$current]->getKey();

            if (! str_contains($current, '.')) {
                break;
            }

            $current = Str::beforeLast($current, '.');
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<string, User>
     */
    private function seedCustomers(): array
    {
        $customers = [
            'greta' => $this->upsertUser('greta@demo.test', 'Greta Mikalajūnaitė'),
            'jonas' => $this->upsertUser('jonas@demo.test', 'Jonas Kazlauskas'),
            'ruta' => $this->upsertUser('ruta@demo.test', 'Rūta Petrauskienė'),
        ];

        Collection::make($customers)->each(static function (User $user): void {
            $user->syncRoles(['user']);
        });

        return $customers;
    }

    private function upsertUser(string $email, string $name): User
    {
        $attributes = User::factory()->state([
            'email' => $email,
            'name' => $name,
            'preferred_locale' => 'lt',
            'is_admin' => false,
        ])->raw();

        return User::query()->updateOrCreate(
            ['email' => $email],
            $attributes,
        );
    }

    /**
     * @param  array<string, User>  $customers
     * @param  array{featured: array<string, Product>, all: array<int, Product>}  $catalog
     */
    private function seedOrders(array $customers, array $catalog, Channel $channel, Zone $zone, Country $country): void
    {
        $featured = $catalog['featured'];
        $allProducts = $catalog['all'];

        $auxiliaryA = Arr::get($allProducts, 5, $featured['comboKit']);
        $auxiliaryB = Arr::get($allProducts, 10, $featured['rotaryHammer']);
        $auxiliaryC = Arr::get($allProducts, 15, $featured['safetyHelmet']);

        $orders = [
            [
                'number' => 'ORD-100001',
                'user' => $customers['greta'],
                'status' => 'completed',
                'payment_status' => 'paid',
                'items' => [
                    ['product' => $featured['rotaryHammer'], 'quantity' => 1],
                    ['product' => $featured['safetyHelmet'], 'quantity' => 2],
                ],
                'shipping' => 12.00,
                'discount' => 0.00,
                'shipping_days' => 2,
            ],
            [
                'number' => 'ORD-100002',
                'user' => $customers['jonas'],
                'status' => 'processing',
                'payment_status' => 'paid',
                'items' => [
                    ['product' => $featured['comboKit'], 'quantity' => 1],
                    ['product' => $auxiliaryA, 'quantity' => 1],
                ],
                'shipping' => 9.50,
                'discount' => 15.00,
                'shipping_days' => null,
            ],
            [
                'number' => 'ORD-100003',
                'user' => $customers['ruta'],
                'status' => 'completed',
                'payment_status' => 'paid',
                'items' => [
                    ['product' => $auxiliaryB, 'quantity' => 1],
                    ['product' => $auxiliaryC, 'quantity' => 3],
                ],
                'shipping' => 14.25,
                'discount' => 5.00,
                'shipping_days' => 3,
            ],
        ];

        foreach ($orders as $orderData) {
            $subtotal = 0.0;

            foreach ($orderData['items'] as $item) {
                $subtotal += round($item['product']->price * $item['quantity'], 2);
            }

            $shipping = $orderData['shipping'];
            $discount = $orderData['discount'];
            $tax = round($subtotal * 0.21, 2);
            $total = round($subtotal + $tax + $shipping - $discount, 2);

            $timestamps = $this->orderTimestamps($orderData['status'], $orderData['shipping_days']);

            $attributes = Order::factory()->state([
                'number' => $orderData['number'],
                'user_id' => $orderData['user']->getKey(),
                'channel_id' => $channel->getKey(),
                'zone_id' => $zone->getKey(),
                'status' => $orderData['status'],
                'payment_status' => $orderData['payment_status'],
                'payment_method' => 'bank_transfer',
                'partner_id' => null,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'discount_amount' => $discount,
                'total' => $total,
                'currency' => 'EUR',
                'billing_address' => $this->addressFor($orderData['user'], $country),
                'shipping_address' => $this->addressFor($orderData['user'], $country),
                'notes' => null,
            ] + $timestamps)->raw();

            $order = Order::query()->updateOrCreate(
                ['number' => $attributes['number']],
                Arr::except($attributes, ['number']),
            );

            $order->items()->delete();

            foreach ($orderData['items'] as $item) {
                /** @var Product $product */
                $product = $item['product'];
                $quantity = $item['quantity'];

                OrderItem::factory()->state([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'product_variant_id' => null,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'price' => $product->price,
                    'total' => round($product->price * $quantity, 2),
                ])->create();
            }
        }
    }

    private function orderTimestamps(string $status, ?int $shippingDays): array
    {
        $now = now();

        return match ($status) {
            'completed' => [
                'shipped_at' => $shippingDays !== null ? $now->copy()->subDays($shippingDays) : $now->copy()->subDays(2),
                'delivered_at' => $now->copy()->subDay(),
            ],
            default => [
                'shipped_at' => null,
                'delivered_at' => null,
            ],
        };
    }

    private function addressFor(User $user, Country $country): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '+37060000000',
            'address' => 'Konstitucijos pr. 7',
            'city' => 'Vilnius',
            'postal_code' => '09308',
            'country' => $country->name,
        ];
    }

    private function syncProductTranslations(Product $product, array $translations): void
    {
        foreach (self::LOCALES as $locale) {
            if (! isset($translations[$locale])) {
                throw new RuntimeException("Missing {$locale} translation for product {$product->slug}");
            }

            $data = $translations[$locale];

            ProductTranslation::query()->updateOrCreate(
                [
                    'product_id' => $product->getKey(),
                    'locale' => $locale,
                ],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'summary' => $data['summary'],
                    'description' => $data['description'],
                    'short_description' => $data['short_description'],
                    'seo_title' => $data['seo_title'],
                    'seo_description' => $data['seo_description'],
                    'meta_keywords' => $data['meta_keywords'],
                    'alt_text' => $data['alt_text'],
                ],
            );
        }
    }

    private function syncProductImages(Product $product, string $slug): void
    {
        ProductImage::withoutGlobalScopes()->where('product_id', $product->getKey())->delete();

        $paths = [
            ['path' => "product-images/{$slug}/hero.jpg", 'alt' => $product->name.' hero'],
            ['path' => "product-images/{$slug}/detail-1.jpg", 'alt' => $product->name.' detail view'],
            ['path' => "product-images/{$slug}/detail-2.jpg", 'alt' => $product->name.' components'],
            ['path' => "product-images/{$slug}/usage.jpg", 'alt' => $product->name.' in use'],
        ];

        foreach ($paths as $index => $data) {
            ProductImage::query()->create([
                'product_id' => $product->getKey(),
                'path' => $data['path'],
                'alt_text' => $data['alt'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function basePlaceholderDefaults(): array
    {
        return [
            'brand' => '',
            'model' => '',
            'series_label' => '',
            'series' => '',
            'power' => '',
            'impact_energy' => '',
            'blows_per_minute' => '',
            'weight' => '',
            'weight_value' => null,
            'disc_diameter' => '',
            'rpm' => '',
            'blade_diameter' => '',
            'cutting_depth' => '',
            'tools_included' => '',
            'battery_capacity' => '',
            'case_type' => '',
            'accuracy' => '',
            'range' => '',
            'self_leveling' => '',
            'pieces' => '',
            'drive_size' => '',
            'material' => '',
            'blade_type' => '',
            'body_material' => '',
            'blade_storage' => '',
            'safety' => '',
            'rating' => '',
            'suspension' => '',
            'accessories' => '',
            'valve' => '',
            'straps' => '',
            'filters' => '',
            'upper_material' => '',
            'outsole' => '',
            'lining' => '',
            'length' => '',
            'gauge' => '',
            'outlets' => '',
            'ip_rating' => '',
            'lumens' => '',
            'power_source' => '',
            'modes' => '',
            'runtime' => '',
            'protocol' => '',
            'load' => '',
            'app_support' => '',
            'voice_control' => '',
            'cutting_width' => '',
            'line_diameter' => '',
            'pressure' => '',
            'flow_rate' => '',
            'motor_type' => '',
            'hose_length' => '',
            'color_temp' => '',
            'type_label' => '',
            'type_display' => '',
        ];
    }

    private function renderTemplate(string $template, array $placeholders): string
    {
        return str_replace(
            array_map(static fn (string $key) => '{'.$key.'}', array_keys($placeholders)),
            array_values($placeholders),
            $template,
        );
    }

    private function categoryLocale(
        string $name,
        string $slug,
        string $description,
        string $shortDescription,
        string $seoTitle,
        string $seoDescription,
        string $seoKeywords
    ): array {
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'short_description' => $shortDescription,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'seo_keywords' => $seoKeywords,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function categoryDefinitions(): array
    {
        return [
            'power-tools' => [
                'slug' => 'power-tools',
                'sort_order' => 10,
                'show_in_menu' => true,
                'translations' => [
                    'en' => $this->categoryLocale(
                        'Power Tools',
                        'power-tools',
                        'Professional-grade power tools covering drilling, cutting, and demolition tasks.',
                        'Cordless and corded power tools built for construction crews.',
                        'Power Tools for Construction Projects',
                        'Shop rotary hammers, saws, and grinders tuned for Baltic job sites.',
                        'power tools, construction tools, baltic'
                    ),
                    'lt' => $this->categoryLocale(
                        'Elektriniai įrankiai',
                        'elektriniai-irankiai',
                        'Profesionalūs elektriniai įrankiai gręžimui, pjovimui ir ardymo darbams.',
                        'Laidiniai ir akumuliatoriniai įrankiai statybų brigadoms.',
                        'Elektriniai įrankiai statybų projektams',
                        'Atraskite perforatorius, pjūklus ir šlifuoklius Baltijos darbų aikštelėms.',
                        'elektriniai įrankiai, statybų įrankiai, baltijos'
                    ),
                    'lv' => $this->categoryLocale(
                        'Elektroinstrumenti',
                        'elektroinstrumenti',
                        'Profesionāli elektroinstrumenti urbšanai, zāģēšanai un demontāžai.',
                        'Tīkla un akumulatora instrumenti būvbrigādēm.',
                        'Elektroinstrumenti būvniecībai',
                        'Iepērciet perforatorus, zāģus un slīpmašīnas Baltijas objektos.',
                        'elektroinstrumenti, būvniecības instrumenti, baltija'
                    ),
                ],
                'children' => [
                    'power-tools.rotary-hammers' => [
                        'slug' => 'rotary-hammers',
                        'sort_order' => 10,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Rotary Hammers',
                                'rotary-hammers',
                                'Heavy-duty rotary hammers and combi drills for concrete.',
                                'Impact tools that deliver fast drilling in reinforced materials.',
                                'Rotary Hammers for Concrete',
                                'Cordless and corded SDS rotary hammers engineered for Baltic contractors.',
                                'rotary hammer, sds plus, concrete drill'
                            ),
                            'lt' => $this->categoryLocale(
                                'Rotaciniai perforatoriai',
                                'rotaciniai-perforatoriai',
                                'Galingi perforatoriai ir kombinuoti grąžtai darbui su betonu.',
                                'Smūginiai įrankiai greitam gręžimui armuotose konstrukcijose.',
                                'Rotaciniai perforatoriai betonui',
                                'Akumuliatoriniai ir laidiniai SDS perforatoriai Baltijos rangovams.',
                                'rotaciniai perforatoriai, sds, betono gręžimas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Rotācijas perforatori',
                                'rotacijas-perforatori',
                                'Jaudīgi perforatori un kombinētās urbjmašīnas betonam.',
                                'Triecieninstrumenti ātrai urbšanai dzelzsbetonā.',
                                'Rotācijas perforatori betonam',
                                'Akumulatora un tīkla SDS perforatori Baltijas būvniekiem.',
                                'rotācijas perforatori, sds, betona urbšana'
                            ),
                        ],
                    ],
                    'power-tools.angle-grinders' => [
                        'slug' => 'angle-grinders',
                        'sort_order' => 20,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Angle Grinders',
                                'angle-grinders',
                                'High-torque grinders for metal fabrication and concrete preparation.',
                                'Compact grinders with safety features for onsite cutting and finishing.',
                                'Angle Grinders for Metal and Concrete',
                                'Browse cordless and corded grinders with brake and kickback control.',
                                'angle grinder, metal grinding, surface prep'
                            ),
                            'lt' => $this->categoryLocale(
                                'Kampiniai šlifuokliai',
                                'kampiniai-slifuokliai',
                                'Didelio sukimo momento šlifuokliai metalo apdirbimui ir betono paruošimui.',
                                'Kompaktiški šlifuokliai su apsaugomis pjovimui ir apdailai.',
                                'Kampiniai šlifuokliai metalui ir betonui',
                                'Rinkitės akumuliatorinius ir laidinius šlifuoklius su stabdžiu ir apsauga nuo smūgių.',
                                'kampiniai šlifuokliai, metalo šlifavimas, paviršiaus paruošimas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Leņķa slīpmašīnas',
                                'lenka-slipmasinas',
                                'Jaudīgas slīpmašīnas metālapstrādei un betona sagatavošanai.',
                                'Kompaktas slīpmašīnas ar drošības funkcijām griešanai un apdarei.',
                                'Leņķa slīpmašīnas metālam un betonam',
                                'Apskatiet akumulatora un tīkla slīpmašīnas ar bremzi un pretatsitiena kontroli.',
                                'leņķa slīpmašīna, metāla slīpēšana, virsmas sagatavošana'
                            ),
                        ],
                    ],
                    'power-tools.circular-saws' => [
                        'slug' => 'circular-saws',
                        'sort_order' => 30,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Circular Saws',
                                'circular-saws',
                                'Precision circular saws for timber framing and sheet goods.',
                                'Rip and cross-cut with accurate depth and bevel adjustments.',
                                'Circular Saws for Carpentry',
                                'Cordless plunge and sidewinder saws ready for Baltic framing crews.',
                                'circular saw, carpentry, framing saw'
                            ),
                            'lt' => $this->categoryLocale(
                                'Diskiniai pjūklai',
                                'diskiniai-pjuklai',
                                'Tikslaus pjovimo diskiniai pjūklai karkasams ir plokštėms.',
                                'Ilgalaikis pjovimas su tiksliu gyliu ir kampo reguliavimu.',
                                'Diskiniai pjūklai dailidei',
                                'Akumuliatoriniai ir laidiniai pjūklai Baltijos karkasų brigadoms.',
                                'diskiniai pjūklai, dailidė, karkaso pjūklas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Ripzāģi',
                                'ripzagi',
                                'Precīzi ripzāģi karkasa būvei un plākšņu materiāliem.',
                                'Precīzi garengriešanai un škērsgriezumiem ar regulējamu dziļumu.',
                                'Ripzāģi galdniekiem',
                                'Akumulatora un tīkla zāģi Baltijas karkasa brigādēm.',
                                'ripzāģis, galdniecība, karkasa zāģis'
                            ),
                        ],
                    ],
                    'power-tools.cordless-kits' => [
                        'slug' => 'cordless-kits',
                        'sort_order' => 40,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Cordless Tool Kits',
                                'cordless-tool-kits',
                                'Multi-tool cordless kits with batteries, chargers, and storage.',
                                'Flexible starter sets covering drilling, driving, and cutting.',
                                'Cordless Tool Kits for Teams',
                                'Bundle key cordless tools with shared batteries for site crews.',
                                'cordless kit, battery tools, combo set'
                            ),
                            'lt' => $this->categoryLocale(
                                'Akumuliatorinių įrankių rinkiniai',
                                'akumuliatoriniu-irankiu-rinkiniai',
                                'Įvairių įrankių rinkiniai su akumuliatoriais, įkrovikliais ir dėžėmis.',
                                'Universalūs startiniai rinkiniai gręžimui, sukimo ir pjovimo darbams.',
                                'Akumuliatoriniai rinkiniai brigadoms',
                                'Sujunkite pagrindinius įrankius ir bendrus akumuliatorius statybvietėje.',
                                'akumuliatoriniai įrankiai, įrankių rinkinys, combo rinkinys'
                            ),
                            'lv' => $this->categoryLocale(
                                'Akumulatoru instrumentu komplekti',
                                'akumulatoru-instrumentu-komplekti',
                                'Daudzfunkcionāli komplekti ar instrumentiem, baterijām un uzlādes ierīcēm.',
                                'Elastīgi starta komplekti urbšanai, skrūvēšanai un zāģēšanai.',
                                'Akumulatoru komplekti brigādēm',
                                'Apvienojiet galvenos instrumentus ar koplietotām baterijām būvlaukumā.',
                                'akumulatoru komplekts, instrumentu komplekts, combo komplekts'
                            ),
                        ],
                    ],
                ],
            ],
            'hand-tools' => [
                'slug' => 'hand-tools',
                'sort_order' => 20,
                'show_in_menu' => true,
                'translations' => [
                    'en' => $this->categoryLocale(
                        'Hand Tools',
                        'hand-tools',
                        'Precision hand tools for layout, fastening, and assembly work.',
                        'Manual tools selected for Baltic carpenters and installers.',
                        'Hand Tools for Craftspeople',
                        'Shop chisels, layout tools, and measuring gear built for site conditions.',
                        'hand tools, layout tools, carpentry'
                    ),
                    'lt' => $this->categoryLocale(
                        'Rankiniai įrankiai',
                        'rankiniai-irankiai',
                        'Tikslūs rankiniai įrankiai žymėjimui, tvirtinimui ir surinkimui.',
                        'Atrinkti rankiniai įrankiai Lietuvos dailidėms ir montuotojams.',
                        'Rankiniai įrankiai meistrams',
                        'Įsigykite kaltus, žymėjimo ir matavimo priemones, pritaikytas statybvietėms.',
                        'rankiniai įrankiai, žymėjimo įrankiai, dailidė'
                    ),
                    'lv' => $this->categoryLocale(
                        'Rokas instrumenti',
                        'rokas-instrumenti',
                        'Precīzi rokas instrumenti marķēšanai, stiprināšanai un montāžai.',
                        'Atlasīti rokas instrumenti Latvijas galdniekiem un montieriem.',
                        'Rokas instrumenti meistariem',
                        'Iegādājieties kaltus, marķēšanas un mērīšanas rīkus pielāgotus būvlaukumiem.',
                        'rokas instrumenti, marķēšanas rīki, galdnieks'
                    ),
                ],
                'children' => [
                    'hand-tools.measurement' => [
                        'slug' => 'measurement-tools',
                        'sort_order' => 10,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Measurement Tools',
                                'measurement-tools',
                                'Laser levels, tapes, and digital measures for precise layout.',
                                'Accurate measurement devices that withstand Baltic weather.',
                                'Measurement Tools for Surveying',
                                'Ensure perfect alignment with lasers, tapes, and smart meters.',
                                'laser level, measuring tape, layout'
                            ),
                            'lt' => $this->categoryLocale(
                                'Matavimo įrankiai',
                                'matavimo-irankiai',
                                'Lazeriniai nivelyrai, ruletės ir matuokliai tiksliam žymėjimui.',
                                'Patikimi matavimo prietaisai, atsparūs Baltijos orams.',
                                'Matavimo įrankiai žymėjimui',
                                'Užtikrinkite tikslą naudodami lazerius, ruletes ir skaitmeninius matuoklius.',
                                'matavimo įrankiai, lazerinis nivelyras, ruletė'
                            ),
                            'lv' => $this->categoryLocale(
                                'Mērīšanas instrumenti',
                                'merisanas-instrumenti',
                                'Lāzera līmeņrāži, mērlentes un digitālie mērinstrumenti precīzam marķējumam.',
                                'Uzticami mērīšanas rīki, kas iztur Baltijas laikapstākļus.',
                                'Mērīšanas instrumenti izkārtošanai',
                                'Nodrošiniet precizitāti ar lāzeriem, lentēm un gudriem mērinstrumentiem.',
                                'mērīšanas instrumenti, lāzera līmeņrādis, mērlente'
                            ),
                        ],
                    ],
                    'hand-tools.wrench-sets' => [
                        'slug' => 'wrench-sets',
                        'sort_order' => 20,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Wrench & Socket Sets',
                                'wrench-socket-sets',
                                'Comprehensive wrench and socket sets for mechanical assembly.',
                                'Organised sets ready for workshop or service-van use.',
                                'Socket Sets for Installers',
                                'Choose metric socket and wrench assortments with durable cases.',
                                'socket set, wrench set, mechanics tools'
                            ),
                            'lt' => $this->categoryLocale(
                                'Raktų ir galvučių rinkiniai',
                                'raktu-ir-galvuciu-rinkiniai',
                                'Išsamūs raktų ir galvučių rinkiniai mechaniniam montavimui.',
                                'Tvarkingi rinkiniai dirbtuvėms ar serviso automobiliui.',
                                'Raktų rinkiniai montuotojams',
                                'Rinkitės metrinius galvučių ir raktų komplektus tvirtose dėkluose.',
                                'raktų rinkinys, galvučių rinkinys, mechaniko įrankiai'
                            ),
                            'lv' => $this->categoryLocale(
                                'Atslēgu un uzgaļu komplekti',
                                'atslegu-un-uzgalu-komplekti',
                                'Plaši atslēgu un uzgaļu komplekti mehāniskai montāžai.',
                                'Sakārtoti komplekti darbnīcai vai servisa busam.',
                                'Atslēgu komplekti montieriem',
                                'Izvēlieties metriskus uzgaļu un atslēgu komplektus izturīgos koferos.',
                                'atslēgu komplekts, uzgaļu komplekts, mehāniķa instrumenti'
                            ),
                        ],
                    ],
                    'hand-tools.cutting-tools' => [
                        'slug' => 'cutting-tools',
                        'sort_order' => 30,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Cutting & Utility Tools',
                                'cutting-utility-tools',
                                'Utility knives, shears, and specialty cutters for finishing tasks.',
                                'Sharp and safe cutting solutions for drywall, flooring, and packaging.',
                                'Cutting Tools for Finish Work',
                                'Upgrade crews with ergonomic knives, snap blades, and safety cutters.',
                                'utility knife, cutting tools, finishing'
                            ),
                            'lt' => $this->categoryLocale(
                                'Pjovimo ir peilių įrankiai',
                                'pjovimo-ir-peiliu-irankiai',
                                'Peiliai, žirklės ir specialūs pjovimo įrankiai apdailos darbams.',
                                'Aštrūs ir saugūs sprendimai gipskartoniui, grindims ir pakuotėms.',
                                'Pjovimo įrankiai apdailai',
                                'Aprūpinkite brigadas ergonomiškais peiliais ir saugiais pjovikliais.',
                                'peiliai, pjovimo įrankiai, apdaila'
                            ),
                            'lv' => $this->categoryLocale(
                                'Griešanas un nazi instrumenti',
                                'griesanas-un-nazi-instrumenti',
                                'Kancelejas naži, šķēres un speciālie griezēji apdares darbiem.',
                                'Asi un droši risinājumi ģipškartonam, grīdām un iepakojumam.',
                                'Griešanas instrumenti apdarei',
                                'Nodrošiniet brigādes ar ergonomiskiem nažiem un drošiem griezējiem.',
                                'naža instruments, griešanas rīki, apdare'
                            ),
                        ],
                    ],
                    'hand-tools.layout-tools' => [
                        'slug' => 'layout-tools',
                        'sort_order' => 40,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Layout & Marking',
                                'layout-and-marking',
                                'Squares, chalk reels, and layout sets for framing and drywall.',
                                'Durable marking kits for precise lines on concrete or timber.',
                                'Layout Tools for Framing',
                                'Deliver straight layouts with squares, levels, and chalk systems.',
                                'layout tools, marking, chalk line'
                            ),
                            'lt' => $this->categoryLocale(
                                'Žymėjimo įrankiai',
                                'zymejimo-irankiai',
                                'Kampainiai, kreidutės ir žymėjimo rinkiniai karkasams ir gipsui.',
                                'Patvarūs žymėjimo rinkiniai tikslioms linijoms betone ir medienoje.',
                                'Žymėjimo įrankiai karkasui',
                                'Darykite tiesias linijas su kampainiais, nivelyrais ir kreidinėmis sistemomis.',
                                'žymėjimo įrankiai, kreida, kampainis'
                            ),
                            'lv' => $this->categoryLocale(
                                'Izkārtojuma un marķēšanas rīki',
                                'izkartojuma-un-markesanas-riki',
                                'Kvadrāti, krīta auklas un komplekti karkasa un ģipškartona darbiem.',
                                'Izturīgi marķēšanas komplekti precīzām līnijām betonā vai kokā.',
                                'Marķēšanas rīki karkasam',
                                'Veidojiet taisnas līnijas ar kvadrātiem, līmeņiem un krīta sistēmām.',
                                'marķēšanas rīki, krīta aukla, kvadrāts'
                            ),
                        ],
                    ],
                ],
            ],
            'building-materials' => [
                'slug' => 'building-materials',
                'sort_order' => 30,
                'show_in_menu' => true,
                'translations' => [
                    'en' => $this->categoryLocale(
                        'Building Materials',
                        'building-materials',
                        'Core construction materials ready for Baltic climates and regulations.',
                        'Drywall, insulation, cement, and roofing components for every phase.',
                        'Building Materials Catalogue',
                        'Source certified materials with logistics support across the Baltics.',
                        'building materials, drywall, insulation'
                    ),
                    'lt' => $this->categoryLocale(
                        'Statybinės medžiagos',
                        'statybines-medziagos',
                        'Pagrindinės statybų medžiagos, pritaikytos Baltijos klimatui ir normoms.',
                        'Gipso kartonas, izoliacija, cementas ir stogo danga kiekvienam etapui.',
                        'Statybinių medžiagų katalogas',
                        'Raskite sertifikuotas medžiagas su logistika visoje Baltijoje.',
                        'statybinės medžiagos, gipsas, izoliacija'
                    ),
                    'lv' => $this->categoryLocale(
                        'Būvmateriāli',
                        'buvmateriali',
                        'Galvenie būvmateriāli, kas pielāgoti Baltijas klimatam un normām.',
                        'Reģipsis, izolācija, cements un jumta segumi katram posmam.',
                        'Būvmateriālu katalogs',
                        'Saņemiet sertificētus materiālus ar loģistiku visā Baltijā.',
                        'būvmateriāli, reģipsis, izolācija'
                    ),
                ],
                'children' => [
                    'building-materials.drywall' => [
                        'slug' => 'drywall',
                        'sort_order' => 10,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Drywall & Plasterboard',
                                'drywall',
                                'Moisture-resistant drywall sheets, profiles, and finishing compounds.',
                                'Complete drywall systems for partitions and ceilings.',
                                'Drywall Systems',
                                'Build interior partitions with Baltic-approved boards and accessories.',
                                'drywall, plasterboard, gypsum'
                            ),
                            'lt' => $this->categoryLocale(
                                'Gipso kartonas',
                                'gipso-kartonas',
                                'Drėgmei atsparios gipso plokštės, profiliai ir glaistai.',
                                'Pilnos gipso kartono sistemos pertvaroms ir luboms.',
                                'Gipso kartono sistemos',
                                'Formuokite pertvaras su Baltijoje patvirtintomis plokštėmis ir priedais.',
                                'gipso kartonas, gipsas, profiliai'
                            ),
                            'lv' => $this->categoryLocale(
                                'Reģipsis un apmetums',
                                'regipsis',
                                'Mitruma izturīgas reģipša loksnes, profili un apdares maisījumi.',
                                'Pilnas reģipša sistēmas starpsienām un griestiem.',
                                'Reģipša sistēmas',
                                'Izveidojiet starpsienas ar Baltijā sertificētiem materiāliem.',
                                'reģipsis, ģipškartons, profili'
                            ),
                        ],
                    ],
                    'building-materials.insulation' => [
                        'slug' => 'insulation',
                        'sort_order' => 20,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Insulation Materials',
                                'insulation',
                                'Mineral wool, PIR boards, and vapor barriers for energy-efficient builds.',
                                'Thermal and acoustic solutions tailored to Baltic climates.',
                                'Insulation for Energy Efficiency',
                                'Meet modern efficiency targets with premium insulation packages.',
                                'insulation, mineral wool, pir boards'
                            ),
                            'lt' => $this->categoryLocale(
                                'Izoliacinės medžiagos',
                                'izoliacines-medziagos',
                                'Mineralinė vata, PIR plokštės ir garo izoliacijos energiją taupantiems pastatams.',
                                'Šilumos ir garso sprendimai pritaikyti Baltijos klimatui.',
                                'Izoliacija energiniam efektyvumui',
                                'Pasiekite efektyvumo tikslus naudodami kokybiškus izoliacijos rinkinius.',
                                'izoliacija, mineralinė vata, pir plokštės'
                            ),
                            'lv' => $this->categoryLocale(
                                'Siltumizolācija',
                                'siltumizolacija',
                                'Minerālvate, PIR plāksnes un tvaika barjeras energoefektīvām ēkām.',
                                'Siltuma un skaņas risinājumi Baltijas klimatam.',
                                'Izolācija energoefektivitātei',
                                'Sasniedziet prasības ar augstas kvalitātes izolācijas komplektiem.',
                                'izolācija, minerālvate, pir plāksnes'
                            ),
                        ],
                    ],
                    'building-materials.cement-mixes' => [
                        'slug' => 'cement-mixes',
                        'sort_order' => 30,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Cement & Mortar Mixes',
                                'cement-mixes',
                                'Ready-mix cement, screeds, and repair mortars for structural work.',
                                'Reliable mixes for floors, masonry, and exterior repairs.',
                                'Cement Mixes and Screeds',
                                'Pour slabs and screeds with Baltic-tested bagged mixes.',
                                'cement, mortar, screed'
                            ),
                            'lt' => $this->categoryLocale(
                                'Cemento ir skiedinių mišiniai',
                                'cemento-ir-skiediniu-misiniai',
                                'Paruošti cemento, grindų ir remonto skiediniai konstrukciniams darbams.',
                                'Patikimi mišiniai grindims, mūrui ir išoriniams remontams.',
                                'Cemento mišiniai ir lygintuvai',
                                'Liekite perdangas ir lygintuvus su Baltijoje patikrintais mišiniais.',
                                'cementas, skiedinys, lygintuvas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Cements un javas',
                                'cements-un-javas',
                                'Gatavi cementa, grīdu un remonta javu maisījumi konstrukciju darbiem.',
                                'Uzticami risinājumi grīdām, mūrim un āra remontiem.',
                                'Cementa maisījumi un izlīdzinošie slāņi',
                                'Betonējiet un izlīdziniet ar Baltijā pārbaudītiem maisījumiem.',
                                'cements, java, izlīdzinošais slānis'
                            ),
                        ],
                    ],
                    'building-materials.roofing' => [
                        'slug' => 'roofing',
                        'sort_order' => 40,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Roofing Systems',
                                'roofing',
                                'Metal panels, membranes, and accessories for weatherproof roofs.',
                                'Complete roofing packages with drainage, safety, and insulation.',
                                'Roofing for Nordic Climates',
                                'Protect structures with systems designed for Baltic snow and wind.',
                                'roofing, metal roof, membrane'
                            ),
                            'lt' => $this->categoryLocale(
                                'Stogo sistemos',
                                'stogo-sistemos',
                                'Metalinių dangų, membranų ir priedų sprendimai sandariam stogui.',
                                'Pilni stogo paketai su lietvamzdžiais, sauga ir izoliacija.',
                                'Stogo sistemos šiauriniam klimatui',
                                'Apsaugokite pastatus sistemomis, pritaikytomis Baltijos sniegui ir vėjui.',
                                'stogo danga, metalinis stogas, membrana'
                            ),
                            'lv' => $this->categoryLocale(
                                'Jumta sistēmas',
                                'jumta-sistemas',
                                'Metāla paneļi, membrānas un piederumi hermētiskiem jumtiem.',
                                'Pilni jumtu komplekti ar lietus ūdens novadi, drošību un izolāciju.',
                                'Jumti ziemeļu klimatam',
                                'Aizsargājiet ēkas ar sistēmām, kas paredzētas Baltijas sniegam un vējam.',
                                'jumta segums, metāla jumts, membrāna'
                            ),
                        ],
                    ],
                ],
            ],
            'safety-equipment' => [
                'slug' => 'safety-equipment',
                'sort_order' => 40,
                'show_in_menu' => true,
                'translations' => [
                    'en' => $this->categoryLocale(
                        'Safety Equipment',
                        'safety-equipment',
                        'Certified safety gear covering head, respiratory, and foot protection.',
                        'Compliant PPE ready for Baltic industrial and construction sites.',
                        'Safety Equipment Catalogue',
                        'Protect crews with helmets, respirators, footwear, and hearing solutions.',
                        'safety equipment, ppe, protective gear'
                    ),
                    'lt' => $this->categoryLocale(
                        'Saugos priemonės',
                        'saugos-priemones',
                        'Sertifikuotos saugos priemonės galvai, kvėpavimo apsaugai ir avalynei.',
                        'Atitinkančios PPE priemonės Baltijos pramonės ir statybų objektams.',
                        'Saugos priemonių katalogas',
                        'Apsaugokite brigadas šalmais, respiratoriais, avalyne ir klausos apsauga.',
                        'saugos priemonės, ppe, apsauginė įranga'
                    ),
                    'lv' => $this->categoryLocale(
                        'Drošības aprīkojums',
                        'drosibas-aprikojums',
                        'Sertificēts drošības aprīkojums galvas, elpošanas un kāju aizsardzībai.',
                        'Atbilstošs PPE Baltijas rūpniecības un būvniecības objektiem.',
                        'Drošības aprīkojuma katalogs',
                        'Aizsargājiet komandas ar ķiverēm, respiratoriem, apaviem un dzirdes aizsardzību.',
                        'drošības aprīkojums, ppe, aizsargaprīkojums'
                    ),
                ],
                'children' => [
                    'safety-equipment.protective-gear' => [
                        'slug' => 'protective-helmets',
                        'sort_order' => 10,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Helmets & Head Protection',
                                'helmets-head-protection',
                                'Industrial helmets with chinstraps, vents, and accessory mounts.',
                                'Head protection compliant with EN standards for high-risk environments.',
                                'Helmets and Headgear',
                                'Equip teams with impact-rated helmets and accessories.',
                                'helmet, head protection, safety helmet'
                            ),
                            'lt' => $this->categoryLocale(
                                'Šalmai ir galvos apsauga',
                                'salmai-ir-galvos-apsauga',
                                'Pramoniniai šalmai su dirželiais, ventiliacija ir priedų tvirtinimu.',
                                'Galvos apsauga, atitinkanti EN standartus sudėtingoms sąlygoms.',
                                'Šalmai ir priedai',
                                'Aprūpinkite komandas smūgiams atspariais šalmais ir priedais.',
                                'šalmas, galvos apsauga, saugos šalmas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Ķiveres un galvas aizsardzība',
                                'kiveres-un-galvas-aizsardziba',
                                'Rūpnieciskās ķiveres ar zoda siksnām, ventilāciju un piederumu stiprinājumiem.',
                                'Galvas aizsardzība, kas atbilst EN standartiem bīstamā vidē.',
                                'Ķiveres un piederumi',
                                'Nodrošiniet komandas ar triecienizturīgām ķiverēm un piederumiem.',
                                'ķivere, galvas aizsardzība, drošības ķivere'
                            ),
                        ],
                    ],
                    'safety-equipment.respiratory-protection' => [
                        'slug' => 'respiratory-protection',
                        'sort_order' => 20,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Respiratory Protection',
                                'respiratory-protection',
                                'Reusable respirators and disposable masks with certified filters.',
                                'Protect crews from dust, fumes, and aerosols on Baltic job sites.',
                                'Respirators and Filters',
                                'Match respirator bodies with P2 and P3 filters for compliant protection.',
                                'respirator, face mask, p3 filter'
                            ),
                            'lt' => $this->categoryLocale(
                                'Kvėpavimo apsauga',
                                'kvepavimo-apsauga',
                                'Daugkartiniai respiratoriai ir vienkartinės kaukės su sertifikuotais filtrais.',
                                'Apsaugokite komandas nuo dulkių, garų ir aerozolių Baltijos objektuose.',
                                'Respiratoriai ir filtrai',
                                'Derinkite respiratorių korpusus su P2 ir P3 filtrais patikimai apsaugai.',
                                'respiratorius, apsauginė kaukė, p3 filtras'
                            ),
                            'lv' => $this->categoryLocale(
                                'Elpošanas aizsardzība',
                                'elposanas-aizsardziba',
                                'Atkārtoti lietojami respiratori un vienreizējas maskas ar sertificētiem filtriem.',
                                'Sargiet komandas no putekļiem, dūmiem un aerosoliem Baltijas objektos.',
                                'Respiratori un filtri',
                                'Savienojiet respiratorus ar P2 un P3 filtriem drošai aizsardzībai.',
                                'respirators, sejas maska, p3 filtrs'
                            ),
                        ],
                    ],
                    'safety-equipment.footwear' => [
                        'slug' => 'safety-footwear',
                        'sort_order' => 30,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Safety Footwear',
                                'safety-footwear',
                                'Protective boots with toe caps, slip resistance, and insulation options.',
                                'Comfortable footwear ready for year-round Baltic weather.',
                                'Safety Boots and Shoes',
                                'Choose S3 and S1P footwear for industrial tasks.',
                                'safety boots, toe cap, s3 footwear'
                            ),
                            'lt' => $this->categoryLocale(
                                'Apsauginė avalynė',
                                'apsaugine-avaline',
                                'Apsauginiai batai su noselėmis, slydimui atspariais padais ir izoliacija.',
                                'Patogi avalynė visiems Baltijos metų laikams.',
                                'Apsauginiai batai ir avalynė',
                                'Rinkitės S3 ir S1P klasės avalynę pramoniniams darbams.',
                                'apsauginiai batai, noselė, s3 avalynė'
                            ),
                            'lv' => $this->categoryLocale(
                                'Drošības apavi',
                                'drosibas-apavi',
                                'Aizsargapavi ar purngala sargiem, pret-slīdes zolēm un izolāciju.',
                                'Ērti apavi visa gada garumā Baltijas apstākļiem.',
                                'Drošības zābaki un apavi',
                                'Izvēlieties S3 un S1P klases apavus industriāliem darbiem.',
                                'drošības apavi, purngala sargs, s3 apavi'
                            ),
                        ],
                    ],
                    'safety-equipment.hearing-protection' => [
                        'slug' => 'hearing-protection',
                        'sort_order' => 40,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Hearing Protection',
                                'hearing-protection',
                                'Ear defenders and in-ear protection with high noise reduction ratings.',
                                'Maintain communication while blocking harmful noise on site.',
                                'Hearing Safety Gear',
                                'Select earmuffs and plugs compatible with helmets and radios.',
                                'hearing protection, earmuffs, ear plugs'
                            ),
                            'lt' => $this->categoryLocale(
                                'Klausos apsauga',
                                'klausos-apsauga',
                                'Ausinės ir į ausis dedami kištukai su dideliu triukšmo slopinimu.',
                                'Išsaugokite komunikaciją ir apsisaugokite nuo triukšmo statybvietėje.',
                                'Klausos apsaugos priemonės',
                                'Pasirinkite ausines ir kištukus, suderinamus su šalmais ir racijomis.',
                                'klausos apsauga, ausinės, ausų kištukai'
                            ),
                            'lv' => $this->categoryLocale(
                                'Dzirdes aizsardzība',
                                'dzirdes-aizsardziba',
                                'Ausu aizsargi un ieliktņi ar augstu trokšņu slāpēšanas pakāpi.',
                                'Saglabājiet saziņu, vienlaikus aizsargājot dzirdi būvlaukumā.',
                                'Dzirdes aizsardzības līdzekļi',
                                'Izvēlieties austiņas un ieliktņus, kas saderīgi ar ķiverēm un radio.',
                                'dzirdes aizsardzība, austiņas, ausu ieliktņi'
                            ),
                        ],
                    ],
                ],
            ],
            'electrical-lighting' => [
                'slug' => 'electrical-lighting',
                'sort_order' => 50,
                'show_in_menu' => true,
                'translations' => [
                    'en' => $this->categoryLocale(
                        'Electrical & Lighting',
                        'electrical-lighting',
                        'Temporary power, cabling, and lighting systems for site operations.',
                        'Distribute safe power and light across indoor and outdoor projects.',
                        'Electrical Distribution & Lighting',
                        'Deploy cabling, work lights, and smart controls tuned for Baltic jobs.',
                        'electrical, lighting, cabling'
                    ),
                    'lt' => $this->categoryLocale(
                        'Elektros ir apšvietimo sprendimai',
                        'elektros-ir-apsvietimo-sprendimai',
                        'Laikinoji elektra, kabeliai ir apšvietimas statybos objektams.',
                        'Saugi energija ir apšvietimas vidaus ir lauko projektams.',
                        'Elektros paskirstymas ir apšvietimas',
                        'Diekite kabelius, darbo apšvietimą ir išmanią kontrolę Baltijos objektams.',
                        'elektra, apšvietimas, kabeliai'
                    ),
                    'lv' => $this->categoryLocale(
                        'Elektroapgāde un apgaismojums',
                        'elektroapgade-un-apgaismojums',
                        'Pagaidu elektroapgāde, kabeļi un apgaismojums būvobjektu vajadzībām.',
                        'Droša elektroapgāde un gaisma iekštelpās un ārā.',
                        'Elektro un apgaismojuma risinājumi',
                        'Ieviesiet kabeļus, darba apgaismojumu un gudru vadību Baltijas objektos.',
                        'elektroapgāde, apgaismojums, kabeļi'
                    ),
                ],
                'children' => [
                    'electrical-lighting.cabling' => [
                        'slug' => 'power-cabling',
                        'sort_order' => 10,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Power Cables & Reels',
                                'power-cables-and-reels',
                                'Heavy-duty extension cords, reels, and distribution blocks.',
                                'Supply temporary power safely across work zones.',
                                'Power Distribution Cables',
                                'Choose IP-rated extension cords and reels for demanding sites.',
                                'extension cord, power cable, reel'
                            ),
                            'lt' => $this->categoryLocale(
                                'Elektros kabeliai ir rites',
                                'elektros-kabeliai-ir-rites',
                                'Patvarūs prailgintuvai, kabelių ritės ir paskirstymo blokai.',
                                'Saugi laikina elektros tiekimo grandinė statybvietėse.',
                                'Elektros paskirstymo kabeliai',
                                'Rinkitės IP klasės prailgintuvus ir rites sudėtingoms sąlygoms.',
                                'prailgintuvas, elektros kabelis, ritė'
                            ),
                            'lv' => $this->categoryLocale(
                                'Elektro kabeļi un ruļļi',
                                'elektro-kabeli-un-rulli',
                                'Izturīgi pagarinātāji, kabeļu ruļļi un sadales bloki.',
                                'Droši nodrošiniet pagaidu elektroapgādi darba zonās.',
                                'Elektroapgādes kabeļi',
                                'Izvēlieties IP klases pagarinātājus un ruļļus prasīgām vietām.',
                                'pagarinātājs, elektro kabelis, ruļļis'
                            ),
                        ],
                    ],
                    'electrical-lighting.work-lighting' => [
                        'slug' => 'work-lighting',
                        'sort_order' => 20,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Work Lighting',
                                'work-lighting',
                                'LED floodlights, tripod lights, and portable site lighting.',
                                'Bright, efficient lighting for interior fit-out and night work.',
                                'LED Work Lights',
                                'Illuminate jobs with adjustable, low-heat LED lighting.',
                                'work light, led floodlight, tripod light'
                            ),
                            'lt' => $this->categoryLocale(
                                'Darbo apšvietimas',
                                'darbo-apsvietimas',
                                'LED prožektoriai, stovai ir nešiojami šviestuvai statybvietėms.',
                                'Ryškus ir efektyvus apšvietimas vidaus darbams ir naktiniams projektams.',
                                'LED darbo šviestuvai',
                                'Apšvieskite objektus reguliuojamais, mažai kaistančiais LED šviestuvais.',
                                'darbo šviestuvas, led prožektorius, statybinis apšvietimas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Darba apgaismojums',
                                'darba-apgaismojums',
                                'LED prožektori, statīvi un pārnēsājami gaismekļi.',
                                'Spilgtā un efektīva gaisma iekštelpu apdarei un nakts darbiem.',
                                'LED darba gaismas',
                                'Apgaismojiet objektus ar regulējamu, zemas temperatūras LED gaismu.',
                                'darba gaisma, led prožektors, statīva gaisma'
                            ),
                        ],
                    ],
                    'electrical-lighting.smart-systems' => [
                        'slug' => 'smart-systems',
                        'sort_order' => 30,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Smart Controls',
                                'smart-controls',
                                'Connected switches, timers, and wireless controllers.',
                                'Automate lighting and power with app-enabled control.',
                                'Smart Electrical Systems',
                                'Upgrade projects with smart switches and load management.',
                                'smart switch, timer, automation'
                            ),
                            'lt' => $this->categoryLocale(
                                'Išmanios valdymo sistemos',
                                'ismanios-valdymo-sistemos',
                                'Išmanieji jungikliai, laikmačiai ir belaidės valdymo sistemos.',
                                'Automatizuokite apšvietimą ir energiją naudodami programėles.',
                                'Išmanios elektros sistemos',
                                'Modernizuokite projektus išmaniais jungikliais ir apkrovų valdymu.',
                                'išmanus jungiklis, laikmatis, automatizavimas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Gudrās vadības sistēmas',
                                'gudras-vadibas-sistemas',
                                'Savienoti slēdži, taimeri un bezvadu kontrolieri.',
                                'Automatizējiet apgaismojumu un elektroapgādi ar lietotņu vadību.',
                                'Gudrie elektro risinājumi',
                                'Uzlabojiet projektus ar gudriem slēdžiem un slodžu vadību.',
                                'gudrais slēdzis, taimeris, automatizācija'
                            ),
                        ],
                    ],
                    'electrical-lighting.switchgear' => [
                        'slug' => 'switchgear',
                        'sort_order' => 40,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Panels & Switchgear',
                                'panels-switchgear',
                                'Distribution panels, breakers, and protection devices.',
                                'Ensure compliant electrical installations for temporary or permanent use.',
                                'Electrical Switchgear',
                                'Select panels and breakers rated for Baltic installations.',
                                'switchgear, breaker, distribution panel'
                            ),
                            'lt' => $this->categoryLocale(
                                'Skydai ir automatiniai jungikliai',
                                'skydai-ir-jungikliai',
                                'Paskirstymo skydai, automatiniai jungikliai ir apsaugos įranga.',
                                'Užtikrinkite normas atitinkančias laikinąsias ir nuolatines instaliacijas.',
                                'Elektros skydai ir apsauga',
                                'Pasirinkite skydus ir jungiklius, pritaikytus Baltijos instaliacijoms.',
                                'elektros skydas, automatinis jungiklis, apsauga'
                            ),
                            'lv' => $this->categoryLocale(
                                'Sadales paneļi un drošinātāji',
                                'sadales-paneli-un-drosinataji',
                                'Sadales paneļi, automātiskie slēdži un aizsardzības ierīces.',
                                'Nodrošiniet atbilstošas elektroinstalācijas pagaidu vai pastāvīgai lietošanai.',
                                'Elektro sadales iekārtas',
                                'Izvēlieties paneļus un slēdžus Baltijas instalācijām.',
                                'sadales panelis, automātiskais slēdzis, aizsardzība'
                            ),
                        ],
                    ],
                ],
            ],
            'outdoor-garden' => [
                'slug' => 'outdoor-garden',
                'sort_order' => 60,
                'show_in_menu' => true,
                'translations' => [
                    'en' => $this->categoryLocale(
                        'Outdoor & Garden',
                        'outdoor-garden',
                        'Outdoor power equipment, water management, and site landscaping tools.',
                        'Maintain grounds and access routes year-round.',
                        'Outdoor Equipment & Garden Care',
                        'Equip crews for landscaping, trimming, and site cleaning.',
                        'outdoor tools, landscaping, garden equipment'
                    ),
                    'lt' => $this->categoryLocale(
                        'Lauko ir sodo technika',
                        'lauko-ir-sodo-technika',
                        'Lauko įrangos, vandens valdymo ir teritorijos priežiūros sprendimai.',
                        'Prižiūrėkite aplinką ir privažiavimus visus metus.',
                        'Lauko įranga ir sodo priežiūra',
                        'Aprūpinkite brigadas kraštovaizdžio, pjovimo ir valymo technika.',
                        'lauko įranga, kraštovaizdis, sodo technika'
                    ),
                    'lv' => $this->categoryLocale(
                        'Āra un dārza aprīkojums',
                        'ara-un-darza-aprikojums',
                        'Āra tehnikas, ūdens apsaimniekošanas un teritoriju kopšanas risinājumi.',
                        'Uzturiet apkārtni un piebraucamos ceļus visu gadu.',
                        'Āra aprīkojums un dārza kopšana',
                        'Nodrošiniet brigādes ar ainavu, pļaušanas un tīrīšanas tehniku.',
                        'ara aprīkojums, ainava, dārza tehnika'
                    ),
                ],
                'children' => [
                    'outdoor-garden.outdoor-power' => [
                        'slug' => 'outdoor-power',
                        'sort_order' => 10,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Outdoor Power Tools',
                                'outdoor-power-tools',
                                'String trimmers, blowers, and battery-powered landscaping tools.',
                                'Maintain green areas with efficient cordless equipment.',
                                'Outdoor Power Equipment',
                                'Deploy trimmers and blowers designed for Baltic landscapes.',
                                'string trimmer, blower, outdoor power'
                            ),
                            'lt' => $this->categoryLocale(
                                'Lauko elektriniai įrankiai',
                                'lauko-elektriniai-irankiai',
                                'Žoliapjovės, pūtikliai ir akumuliatoriniai kraštovaizdžio įrankiai.',
                                'Prižiūrėkite žaliąsias zonas efektyvia akumuliatorine technika.',
                                'Lauko elektrinė technika',
                                'Naudokite pjovimo ir pūtimo įrankius, pritaikytus Baltijos kraštovaizdžiui.',
                                'žoliapjovė, pūtiklis, lauko įrankiai'
                            ),
                            'lv' => $this->categoryLocale(
                                'Āra elektroinstrumenti',
                                'ara-elektroinstrumenti',
                                'Zāles trimmeri, lapu pūtēji un akumulatora ainavu instrumenti.',
                                'Kopiet zaļās zonas ar efektīvu akumulatora tehniku.',
                                'Āra elektro aprīkojums',
                                'Izmantojiet trimmerus un pūtējus, kas pielāgoti Baltijas ainavai.',
                                'zāles trimmeris, lapu pūtējs, āra instruments'
                            ),
                        ],
                    ],
                    'outdoor-garden.water-management' => [
                        'slug' => 'water-management',
                        'sort_order' => 20,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Water Management',
                                'water-management',
                                'Pressure washers, hoses, and pumps for cleaning and irrigation.',
                                'Control water for site cleaning and landscape care.',
                                'Water & Cleaning Systems',
                                'Select washers and pumping solutions for Baltic facilities.',
                                'pressure washer, hose, pump'
                            ),
                            'lt' => $this->categoryLocale(
                                'Vandens valdymas',
                                'vandens-valdymas',
                                'Aukšto slėgio plovyklos, žarnos ir siurbliai valymui ir laistymui.',
                                'Valdykite vandenį teritorijos valymui ir priežiūrai.',
                                'Vandens ir valymo sistemos',
                                'Pasirinkite plovyklas ir siurblius Baltijos objektams.',
                                'aukšto slėgio plovykla, žarna, siurblys'
                            ),
                            'lv' => $this->categoryLocale(
                                'Ūdens apsaimniekošana',
                                'udens-apsaimniekosana',
                                'Augstspiediena mazgātāji, šļūtenes un sūkņi tīrīšanai un laistīšanai.',
                                'Kontrolējiet ūdeni teritorijas tīrīšanai un ainavu kopšanai.',
                                'Ūdens un tīrīšanas sistēmas',
                                'Izvēlieties mazgātājus un sūkņus Baltijas objektos.',
                                'augstspiediena mazgātājs, šļūtene, sūknis'
                            ),
                        ],
                    ],
                    'outdoor-garden.landscaping' => [
                        'slug' => 'landscaping',
                        'sort_order' => 30,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Landscaping Accessories',
                                'landscaping-accessories',
                                'Edging, lighting, and storage solutions for organised outdoor areas.',
                                'Finish sites with lighting and edging that withstand the climate.',
                                'Landscaping Accessories',
                                'Add finishing touches with durable edging and outdoor lighting.',
                                'landscaping, edging, garden lighting'
                            ),
                            'lt' => $this->categoryLocale(
                                'Kraštovaizdžio priedai',
                                'krastovaizdzio-priedai',
                                'Bordiūrai, apšvietimas ir saugojimo sprendimai tvarkingoms teritorijoms.',
                                'Užbaikite teritorijas apšvietimu ir kraštų sutvirtinimu.',
                                'Kraštovaizdžio aksesuarai',
                                'Papildykite projektus patvariais bordiūrais ir lauko apšvietimu.',
                                'kraštovaizdis, bordiūras, lauko apšvietimas'
                            ),
                            'lv' => $this->categoryLocale(
                                'Ainavas piederumi',
                                'ainavas-piederumi',
                                'Apmales, apgaismojums un glabāšanas risinājumi sakārtotām teritorijām.',
                                'Pabeidziet objektus ar apgaismojumu un apmalēm, kas iztur klimatiskos apstākļus.',
                                'Ainavu aksesuāri',
                                'Pievienojiet gala akcentus ar izturīgām apmalēm un āra apgaismojumu.',
                                'ainavas piederumi, apmale, dārza apgaismojums'
                            ),
                        ],
                    ],
                    'outdoor-garden.storage' => [
                        'slug' => 'outdoor-storage',
                        'sort_order' => 40,
                        'translations' => [
                            'en' => $this->categoryLocale(
                                'Outdoor Storage',
                                'outdoor-storage',
                                'Weatherproof boxes, racks, and shelters for equipment and consumables.',
                                'Keep gear organised and secure on job sites.',
                                'Outdoor Storage Solutions',
                                'Store tools and supplies in weather-resistant enclosures.',
                                'outdoor storage, site box, weatherproof'
                            ),
                            'lt' => $this->categoryLocale(
                                'Lauko sandėliavimas',
                                'lauko-sandeliavimas',
                                'Atsparios dėžės, stelažai ir pastogės įrangai bei medžiagoms.',
                                'Laikykite įrankius tvarkingai ir saugiai statybvietėje.',
                                'Lauko sandėliavimo sprendimai',
                                'Sandėliuokite įrangą atspariose oro sąlygoms talpyklose.',
                                'lauko sandėlis, dėžė, atsparus orui'
                            ),
                            'lv' => $this->categoryLocale(
                                'Āra glabātuves',
                                'ara-glabatves',
                                'Laikapstākļiem izturīgas kastes, plaukti un nojumes aprīkojumam un materiāliem.',
                                'Uzglabājiet aprīkojumu sakārtoti un droši būvlaukumā.',
                                'Āra glabāšanas risinājumi',
                                'Glabājiet instrumentus un materiālus laikapstākļiem izturīgās tvertnēs.',
                                'āra glabātuve, kaste, laikapstākļu izturīgs'
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }
    /**
     * @return array<string, array<string, mixed>>
     */
    private function productTypeConfigurations(): array
    {
        return [
            'rotary-hammer' => [
                'category_key' => 'power-tools.rotary-hammers',
                'sku_prefix' => 'PT-RH',
                'dimensions' => ['length' => 42.0, 'width' => 12.0, 'height' => 26.0],
                'default_weight' => 3.6,
                'stock' => ['base' => 28, 'step' => 6],
                'type_label' => [
                    'en' => 'rotary hammer',
                    'lt' => 'rotacinis perforatorius',
                    'lv' => 'rotācijas perforators',
                ],
                'type_display' => [
                    'en' => 'Rotary Hammer',
                    'lt' => 'Rotacinis perforatorius',
                    'lv' => 'Rotācijas perforators',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Brushless {type_label} delivering {impact_energy} impact energy on the {power} platform.',
                        'summary' => 'Cordless {type_label} engineered for demanding concrete drilling and chiselling.',
                        'description' => '<p>The {brand} {model} {series_label}{type_label} is built for crews that need consistent impact power.</p><ul><li>{impact_energy} impact energy with {blows_per_minute} blows per minute.</li><li>Balanced housing weighing only {weight} to reduce fatigue.</li><li>Runs on the {power} ecosystem so batteries swap across the fleet.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {power}',
                        'seo_description' => 'Order the {brand} {model} {type_label} with {impact_energy} impact energy for fast concrete drilling.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Bešepetėlis {type_label} su {impact_energy} smūgio energija ir {power} platforma.',
                        'summary' => 'Akumuliatorinis {type_label}, skirtas intensyviam gręžimui ir kalimui betone.',
                        'description' => '<p>{brand} {model} {series_label}{type_label} sukurtas brigadoms, kurioms reikia patikimos smūgio galios.</p><ul><li>{impact_energy} smūgio energija ir iki {blows_per_minute} smūgių per minutę.</li><li>Subalansuotas korpusas sveria tik {weight}, todėl sumažina nuovargį.</li><li>Veikia su {power} platforma, todėl baterijos keičiamos tarp įrankių.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {power}',
                        'seo_description' => 'Įsigykite {brand} {model} {type_label} su {impact_energy} smūgio energija greitam darbui betone.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Bezoglītes {type_label} ar {impact_energy} trieciena enerģiju un {power} platformu.',
                        'summary' => 'Akumulatora {type_label}, kas paredzēts intensīvai urbšanai un kalšanai betonā.',
                        'description' => '<p>{brand} {model} {series_label}{type_label} izstrādāts brigādēm, kurām nepieciešama stabila trieciena jauda.</p><ul><li>{impact_energy} trieciena enerģija un līdz {blows_per_minute} triecieniem minūtē.</li><li>Līdzsvarots korpuss, kas sver tikai {weight}, samazina nogurumu.</li><li>Darbojas ar {power} platformu, tāpēc baterijas var dalīt starp instrumentiem.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {power}',
                        'seo_description' => 'Pasūtiet {brand} {model} {type_label} ar {impact_energy} trieciena enerģiju ātrai urbšanai betonā.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'makita',
                        'series' => 'XGT',
                        'spec' => [
                            'power' => '40V XGT',
                            'blows_per_minute' => '0-4800 bpm',
                        ],
                        'items' => [
                            ['model' => 'HR010G', 'impact_energy' => '3.2 J', 'weight_kg' => 3.8, 'price' => 349.00, 'sale_price' => 329.00],
                            ['model' => 'HR009G', 'impact_energy' => '2.9 J', 'weight_kg' => 3.6, 'price' => 329.00],
                            ['model' => 'HR008G', 'impact_energy' => '2.7 J', 'weight_kg' => 3.5, 'price' => 309.00],
                        ],
                    ],
                    [
                        'brand' => 'bosch',
                        'series' => 'PROFACTOR',
                        'spec' => [
                            'power' => '18V ProCORE',
                            'blows_per_minute' => '0-4600 bpm',
                        ],
                        'items' => [
                            ['model' => 'GBH18V-34', 'impact_energy' => '3.4 J', 'weight_kg' => 3.7, 'price' => 359.00],
                            ['model' => 'GBH18V-28', 'impact_energy' => '2.8 J', 'weight_kg' => 3.5, 'price' => 329.00],
                            ['model' => 'GBH18V-26', 'impact_energy' => '2.6 J', 'weight_kg' => 3.3, 'price' => 309.00],
                        ],
                    ],
                    [
                        'brand' => 'hilti',
                        'series' => 'Nuron',
                        'spec' => [
                            'power' => '22V Nuron',
                            'blows_per_minute' => '0-5000 bpm',
                        ],
                        'items' => [
                            ['model' => 'TE 60-22', 'impact_energy' => '6.0 J', 'weight_kg' => 4.9, 'price' => 499.00, 'sale_price' => 469.00],
                            ['model' => 'TE 50-22', 'impact_energy' => '5.0 J', 'weight_kg' => 4.6, 'price' => 469.00],
                            ['model' => 'TE 30-22', 'impact_energy' => '3.6 J', 'weight_kg' => 4.2, 'price' => 439.00],
                        ],
                    ],
                ],
            ],
            'angle-grinder' => [
                'category_key' => 'power-tools.angle-grinders',
                'sku_prefix' => 'PT-AG',
                'dimensions' => ['length' => 36.0, 'width' => 11.5, 'height' => 13.5],
                'default_weight' => 2.4,
                'stock' => ['base' => 32, 'step' => 5],
                'type_label' => [
                    'en' => 'angle grinder',
                    'lt' => 'kampinis šlifuoklis',
                    'lv' => 'leņķa slīpmašīna',
                ],
                'type_display' => [
                    'en' => 'Angle Grinder',
                    'lt' => 'Kampinis šlifuoklis',
                    'lv' => 'Leņķa slīpmašīna',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Compact {type_label} with {disc_diameter} disc capacity and {rpm} motor speed.',
                        'summary' => 'Designed for cutting and grinding metal or masonry with controlled torque.',
                        'description' => '<p>The {brand} {model} grinder keeps productivity high with a slim grip and safety brake.</p><ul><li>{disc_diameter} cutting capacity supported by a {rpm} brushless motor.</li><li>Weighs {weight} for fatigue-free overhead work.</li><li>Part of the {power} system for shared batteries and chargers.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {disc_diameter} disc',
                        'seo_description' => 'Get the {brand} {model} {type_label} with {disc_diameter} disc size and {rpm} speed for precise cutting.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Kompaktiškas {type_label} su {disc_diameter} disku ir {rpm} variklio greičiu.',
                        'summary' => 'Sukurtas metalo ir mūro pjovimui bei šlifavimui su valdomu sukimo momentu.',
                        'description' => '<p>{brand} {model} šlifuoklis užtikrina našumą dėl plonos rankenos ir saugos stabdžio.</p><ul><li>{disc_diameter} disko talpa ir {rpm} bešepetėlio variklio greitis.</li><li>Sveria {weight}, todėl patogu dirbti virš galvos.</li><li>Priklauso {power} sistemai, todėl baterijas galima keisti tarp įrankių.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {disc_diameter} diskas',
                        'seo_description' => 'Įsigykite {brand} {model} {type_label} su {disc_diameter} disku ir {rpm} greičiu tiksliam pjovimui.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Kompakta {type_label} ar {disc_diameter} disku un {rpm} motora ātrumu.',
                        'summary' => 'Paredzēta metāla un mūra griešanai vai slīpēšanai ar kontrolētu griezes momentu.',
                        'description' => '<p>{brand} {model} slīpmašīna nodrošina produktivitāti ar šauru korpusu un drošības bremzi.</p><ul><li>{disc_diameter} diska kapacitāte un {rpm} bezoglītes motors.</li><li>Svars {weight}, lai samazinātu nogurumu virs galvas darbos.</li><li>Darbojas {power} sistēmā koplietojamām baterijām.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {disc_diameter} disks',
                        'seo_description' => 'Iegādājieties {brand} {model} {type_label} ar {disc_diameter} disku un {rpm} ātrumu precīzai griešanai.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'dewalt',
                        'series' => 'XR',
                        'spec' => [
                            'power' => '18V XR',
                        ],
                        'items' => [
                            ['model' => 'DCG418', 'disc_diameter' => '125 mm', 'rpm' => '9000 rpm', 'weight_kg' => 2.4, 'price' => 269.00, 'sale_price' => 249.00],
                            ['model' => 'DCG409', 'disc_diameter' => '125 mm', 'rpm' => '8500 rpm', 'weight_kg' => 2.3, 'price' => 249.00],
                            ['model' => 'DCG405', 'disc_diameter' => '125 mm', 'rpm' => '9000 rpm', 'weight_kg' => 2.2, 'price' => 219.00],
                        ],
                    ],
                    [
                        'brand' => 'milwaukee',
                        'series' => 'M18 FUEL',
                        'spec' => [
                            'power' => 'M18 FUEL',
                        ],
                        'items' => [
                            ['model' => 'M18 CAG125XPDB', 'disc_diameter' => '125 mm', 'rpm' => '8500 rpm', 'weight_kg' => 2.5, 'price' => 279.00],
                            ['model' => 'M18 FSAGV125', 'disc_diameter' => '125 mm', 'rpm' => '9000 rpm', 'weight_kg' => 2.6, 'price' => 289.00],
                            ['model' => 'M18 CAG100', 'disc_diameter' => '100 mm', 'rpm' => '11000 rpm', 'weight_kg' => 2.2, 'price' => 239.00],
                        ],
                    ],
                    [
                        'brand' => 'metabo',
                        'series' => 'LiHD',
                        'spec' => [
                            'power' => '18V LiHD',
                        ],
                        'items' => [
                            ['model' => 'WPB 18 LTX BL 125', 'disc_diameter' => '125 mm', 'rpm' => '9000 rpm', 'weight_kg' => 2.5, 'price' => 259.00],
                            ['model' => 'WB 18 LTX BL 180', 'disc_diameter' => '180 mm', 'rpm' => '8000 rpm', 'weight_kg' => 2.9, 'price' => 289.00],
                            ['model' => 'W 18 L 9-125', 'disc_diameter' => '125 mm', 'rpm' => '9000 rpm', 'weight_kg' => 2.4, 'price' => 229.00],
                        ],
                    ],
                ],
            ],

            'circular-saw' => [
                'category_key' => 'power-tools.circular-saws',
                'sku_prefix' => 'PT-CS',
                'dimensions' => ['length' => 45.0, 'width' => 24.0, 'height' => 28.0],
                'default_weight' => 4.5,
                'stock' => ['base' => 30, 'step' => 4],
                'type_label' => [
                    'en' => 'circular saw',
                    'lt' => 'diskinis pjūklas',
                    'lv' => 'ripzāģis',
                ],
                'type_display' => [
                    'en' => 'Circular Saw',
                    'lt' => 'Diskinis pjūklas',
                    'lv' => 'Ripzāģis',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Cordless {type_label} with {blade_diameter} blade and {cutting_depth} cutting depth.',
                        'summary' => 'Engineered for framing and sheet goods with precise bevel adjustments.',
                        'description' => '<p>The {brand} {model} delivers straight cuts with guided rails and clean plunge action.</p><ul><li>{blade_diameter} blade paired with {power} drive for smooth cuts.</li><li>Accurate {cutting_depth} depth control keeps tear-out low.</li><li>Balanced design at {weight} for all-day framing work.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {blade_diameter} blade',
                        'seo_description' => 'Purchase the {brand} {model} {type_label} with {blade_diameter} blade and {cutting_depth} depth for efficient framing.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Akumuliatorinis {type_label} su {blade_diameter} disku ir {cutting_depth} pjovimo gyliu.',
                        'summary' => 'Sukurtas karkasų ir plokščių pjovimui su tiksliu nuolydžio reguliavimu.',
                        'description' => '<p>{brand} {model} užtikrina tiesius pjūvius su kreipiančiomis ir švariu įgilintu pjovimu.</p><ul><li>{blade_diameter} diskas ir {power} pavara lygiam pjovimui.</li><li>Tikslus {cutting_depth} gylio reguliavimas sumažina išplėšimus.</li><li>Subalansuota konstrukcija, sverianti {weight}, leidžia dirbti visą dieną.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {blade_diameter} diskas',
                        'seo_description' => 'Įsigykite {brand} {model} {type_label} su {blade_diameter} disku ir {cutting_depth} gyliu efektyviam karkaso pjovimui.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Akumulatora {type_label} ar {blade_diameter} ripu un {cutting_depth} griešanas dziļumu.',
                        'summary' => 'Paredzēts karkasa un plākšņu materiāliem ar precīzu slīpuma regulēšanu.',
                        'description' => '<p>{brand} {model} nodrošina taisnus griezumus ar vadotnēm un tīru iegremdējamu darbību.</p><ul><li>{blade_diameter} ripas un {power} piedziņa vienmērīgiem griezumiem.</li><li>Precīza {cutting_depth} dziļuma kontrole mazina šķembas.</li><li>Līdzsvarots dizains ar {weight} svaru ļauj strādāt visu dienu.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {blade_diameter} ripa',
                        'seo_description' => 'Iegādājieties {brand} {model} {type_label} ar {blade_diameter} ripu un {cutting_depth} dziļumu efektīvai karkasa zāģēšanai.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'festool',
                        'series' => 'TSC',
                        'spec' => [
                            'power' => '18V Twin Battery',
                        ],
                        'items' => [
                            ['model' => 'TSC 55 K', 'blade_diameter' => '165 mm', 'cutting_depth' => '55 mm', 'power' => '2x18V', 'weight_kg' => 4.6, 'price' => 589.00, 'sale_price' => 559.00],
                            ['model' => 'HKC 55', 'blade_diameter' => '160 mm', 'cutting_depth' => '55 mm', 'power' => '18V', 'weight_kg' => 4.1, 'price' => 479.00],
                            ['model' => 'TS 60 K', 'blade_diameter' => '168 mm', 'cutting_depth' => '60 mm', 'power' => '230V AC', 'weight_kg' => 4.7, 'price' => 619.00],
                        ],
                    ],
                    [
                        'brand' => 'makita',
                        'series' => 'LXT',
                        'spec' => [
                            'power' => '18V LXT',
                        ],
                        'items' => [
                            ['model' => 'HS004G', 'blade_diameter' => '190 mm', 'cutting_depth' => '68 mm', 'power' => '40V XGT', 'weight_kg' => 4.7, 'price' => 349.00],
                            ['model' => 'DHS780', 'blade_diameter' => '260 mm', 'cutting_depth' => '68 mm', 'power' => '2x18V LXT', 'weight_kg' => 5.1, 'price' => 399.00],
                            ['model' => 'DHS680', 'blade_diameter' => '165 mm', 'cutting_depth' => '57 mm', 'power' => '18V LXT', 'weight_kg' => 3.3, 'price' => 259.00],
                        ],
                    ],
                    [
                        'brand' => 'dewalt',
                        'series' => 'XR FlexVolt',
                        'spec' => [
                            'power' => '54V XR FlexVolt',
                        ],
                        'items' => [
                            ['model' => 'DCS578', 'blade_diameter' => '190 mm', 'cutting_depth' => '67 mm', 'power' => '54V FlexVolt', 'weight_kg' => 4.8, 'price' => 369.00],
                            ['model' => 'DCS520', 'blade_diameter' => '165 mm', 'cutting_depth' => '59 mm', 'power' => '54V FlexVolt', 'weight_kg' => 4.7, 'price' => 449.00],
                            ['model' => 'DCS565', 'blade_diameter' => '165 mm', 'cutting_depth' => '55 mm', 'power' => '18V XR', 'weight_kg' => 3.2, 'price' => 239.00],
                        ],
                    ],
                ],
            ],

            'combo-kit' => [
                'category_key' => 'power-tools.cordless-kits',
                'sku_prefix' => 'PT-CK',
                'dimensions' => ['length' => 55.0, 'width' => 35.0, 'height' => 32.0],
                'default_weight' => 11.0,
                'stock' => ['base' => 24, 'step' => 3],
                'type_label' => [
                    'en' => 'combo kit',
                    'lt' => 'įrankių rinkinys',
                    'lv' => 'instrumentu komplekts',
                ],
                'type_display' => [
                    'en' => 'Cordless Combo Kit',
                    'lt' => 'Akumuliatorinių įrankių rinkinys',
                    'lv' => 'Akumulatoru instrumentu komplekts',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => '{tools_included} with {battery_capacity} batteries and {case_type}.',
                        'summary' => 'Complete {power} {type_label} for multi-trade crews with shared batteries.',
                        'description' => '<p>The {brand} {model} kit keeps crews productive from drilling to cutting.</p><ul><li>{tools_included} cover daily jobsite tasks.</li><li>Supplied with {battery_capacity} batteries on the {power} system.</li><li>Delivered in {case_type} for organised transport.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {tools_included}',
                        'seo_description' => 'Bundle the {brand} {model} kit with {battery_capacity} batteries and {case_type} storage on the {power} platform.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => '{tools_included} su {battery_capacity} akumuliatoriais ir {case_type}.',
                        'summary' => 'Pilnas {power} {type_label} skirtingoms brigadoms su bendromis baterijomis.',
                        'description' => '<p>{brand} {model} rinkinys padeda komandoms nuo gręžimo iki pjovimo.</p><ul><li>{tools_included} padengia kasdienius darbų aikštelės poreikius.</li><li>Pridėti {battery_capacity} akumuliatoriai {power} platformai.</li><li>Supakuota į {case_type} patogiam transportavimui.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {tools_included}',
                        'seo_description' => 'Rinkinyje yra {battery_capacity} akumuliatoriai ir {case_type} dėklai {power} platformai.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => '{tools_included} ar {battery_capacity} baterijām un {case_type}.',
                        'summary' => 'Pilns {power} {type_label} dažādu darbu komandām ar kopīgām baterijām.',
                        'description' => '<p>{brand} {model} komplekts uztur produktivitāti no urbšanas līdz zāģēšanai.</p><ul><li>{tools_included} nosedz ikdienas būvlaukuma uzdevumus.</li><li>Iekļautas {battery_capacity} baterijas {power} platformai.</li><li>Piegādāts {case_type} ērtai pārvadāšanai.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {tools_included}',
                        'seo_description' => 'Komplekts ar {battery_capacity} baterijām un {case_type} glabāšanu {power} platformai.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'makita',
                        'series' => 'LXT',
                        'spec' => [
                            'power' => '18V LXT',
                        ],
                        'items' => [
                            ['model' => 'DLX4157T', 'tools_included' => 'Hammer drill, impact driver, circular saw, worklight', 'battery_capacity' => '2x5.0Ah', 'case_type' => 'Makpac stackable cases', 'weight_kg' => 10.8, 'price' => 699.00, 'sale_price' => 659.00],
                            ['model' => 'DLX3075M', 'tools_included' => 'Drill driver, impact driver, reciprocating saw', 'battery_capacity' => '2x4.0Ah', 'case_type' => 'Makpac twin case', 'weight_kg' => 9.4, 'price' => 569.00],
                            ['model' => 'DLX2145TJ', 'tools_included' => 'Combi drill, impact driver', 'battery_capacity' => '2x5.0Ah', 'case_type' => 'Makpac carry case', 'weight_kg' => 6.2, 'price' => 459.00],
                        ],
                    ],
                    [
                        'brand' => 'dewalt',
                        'series' => 'XR',
                        'spec' => [
                            'power' => '18V XR',
                        ],
                        'items' => [
                            ['model' => 'DCK854P4', 'tools_included' => 'Hammer drill, impact driver, grinder, oscillating tool', 'battery_capacity' => '4x5.0Ah', 'case_type' => 'TSTAK rolling box', 'weight_kg' => 12.4, 'price' => 799.00, 'sale_price' => 749.00],
                            ['model' => 'DCK755P3T', 'tools_included' => 'Drill driver, circular saw, LED light, speaker', 'battery_capacity' => '3x5.0Ah', 'case_type' => 'TSTAK organiser', 'weight_kg' => 11.6, 'price' => 699.00],
                            ['model' => 'DCK425D2', 'tools_included' => 'Drill driver, impact driver, worklight, radio', 'battery_capacity' => '2x2.0Ah', 'case_type' => 'ToughSystem tote', 'weight_kg' => 8.9, 'price' => 449.00],
                        ],
                    ],
                    [
                        'brand' => 'milwaukee',
                        'series' => 'M18 FUEL',
                        'spec' => [
                            'power' => 'M18 FUEL',
                        ],
                        'items' => [
                            ['model' => 'M18FPP6A2-503B', 'tools_included' => 'Impact driver, hammer drill, grinder, circular saw', 'battery_capacity' => '3x5.0Ah', 'case_type' => 'Packout roller box', 'weight_kg' => 11.8, 'price' => 829.00],
                            ['model' => 'M18FPP4A2-502B', 'tools_included' => 'Hammer drill, impact driver, recip saw, LED light', 'battery_capacity' => '2x5.0Ah', 'case_type' => 'Packout toolbox', 'weight_kg' => 10.7, 'price' => 699.00],
                            ['model' => 'M18FPP2A2-502X', 'tools_included' => 'Hammer drill, impact driver', 'battery_capacity' => '2x5.0Ah', 'case_type' => 'Packout compact box', 'weight_kg' => 6.9, 'price' => 499.00],
                        ],
                    ],
                ],
            ],

            'laser-level' => [
                'category_key' => 'hand-tools.measurement',
                'sku_prefix' => 'HT-LL',
                'dimensions' => ['length' => 20.0, 'width' => 15.0, 'height' => 15.0],
                'default_weight' => 1.5,
                'stock' => ['base' => 40, 'step' => 6],
                'type_label' => [
                    'en' => 'laser level',
                    'lt' => 'lazerinis nivelyras',
                    'lv' => 'lāzera līmeņrādis',
                ],
                'type_display' => [
                    'en' => 'Laser Level',
                    'lt' => 'Lazerinis nivelyras',
                    'lv' => 'Lāzera līmeņrādis',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Self-leveling {type_label} with {accuracy} accuracy across {range} working distance.',
                        'summary' => 'Bright beams with {modes} modes powered by {power_source}.',
                        'description' => '<p>Set reference lines indoors and outdoors with the {brand} {model}.</p><ul><li>{self_leveling} keeps lines true within {accuracy}.</li><li>Projects up to {range} with flexible {modes} modes.</li><li>Runs on {power_source} for {runtime} continuous operation.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {accuracy} accuracy',
                        'seo_description' => 'Level faster with the {brand} {model} {type_label} offering {accuracy} precision and {range} range.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Savaime išsilyginantis {type_label} su {accuracy} tikslumu ir {range} atstumu.',
                        'summary' => 'Ryškūs spinduliai su {modes} režimais, maitinami {power_source}.',
                        'description' => '<p>Nustatykite atskaitos linijas su {brand} {model} įrenginiu.</p><ul><li>{self_leveling} palaiko tikslumą iki {accuracy}.</li><li>Projektuoja iki {range} su lanksčiais {modes} režimais.</li><li>Veikia su {power_source} iki {runtime} nepertraukiamo darbo.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {accuracy} tikslumas',
                        'seo_description' => 'Darbus paspartinkite su {brand} {model} {type_label}, kuris pasižymi {accuracy} tikslumu ir {range} nuotoliu.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Pašizlīdzinošs {type_label} ar {accuracy} precizitāti un {range} darbības attālumu.',
                        'summary' => 'Spilgtas starojums ar {modes} režīmiem, ko nodrošina {power_source}.',
                        'description' => '<p>Iestatiet atsauces līnijas ar {brand} {model} instrumentu.</p><ul><li>{self_leveling} saglabā precizitāti līdz {accuracy}.</li><li>Projecē līdz {range} ar pielāgojamiem {modes} režīmiem.</li><li>Darbība no {power_source} līdz {runtime} nepārtraukti.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {accuracy} precizitāte',
                        'seo_description' => 'Izlīdziniet ātrāk ar {brand} {model} {type_label}, nodrošinot {accuracy} precizitāti un {range} diapazonu.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'bosch',
                        'series' => 'Professional',
                        'spec' => [
                            'power_source' => '12V Li-ion',
                        ],
                        'items' => [
                            ['model' => 'GCL 2-50 G', 'accuracy' => '±0.3 mm/m', 'range' => '35 m', 'self_leveling' => '±4° self-levelling with alarm', 'modes' => 'cross-line with plumb points', 'runtime' => 'up to 8 h', 'power_source' => '12V Li-ion pack', 'price' => 329.00],
                            ['model' => 'GLL 3-80 C', 'accuracy' => '±0.2 mm/m', 'range' => '30 m', 'self_leveling' => '±4° auto levelling', 'modes' => '3 x 360° planes', 'runtime' => 'up to 6 h', 'power_source' => '12V Li-ion or AA adapter', 'price' => 389.00],
                            ['model' => 'GTL 3', 'accuracy' => '±0.4 mm/m', 'range' => '20 m', 'self_leveling' => '±4° levelling with tilt function', 'modes' => 'tile layout cross + 45°', 'runtime' => 'up to 12 h', 'power_source' => 'AA batteries', 'price' => 269.00],
                        ],
                    ],
                    [
                        'brand' => 'dewalt',
                        'series' => 'XR',
                        'spec' => [
                            'power_source' => '12V XR Li-ion',
                        ],
                        'items' => [
                            ['model' => 'DCE089D1G', 'accuracy' => '±0.2 mm/m', 'range' => '30 m', 'self_leveling' => '±4° auto levelling', 'modes' => '3 x 360° green beams', 'runtime' => 'up to 10 h', 'price' => 459.00, 'sale_price' => 439.00],
                            ['model' => 'DCE088D1R', 'accuracy' => '±0.3 mm/m', 'range' => '50 m with detector', 'self_leveling' => '±4° self levelling', 'modes' => 'horizontal and vertical red lines', 'runtime' => 'up to 8 h', 'price' => 289.00],
                            ['model' => 'DW088CG', 'accuracy' => '±0.3 mm/m', 'range' => '30 m', 'self_leveling' => '±4° levelling', 'modes' => 'cross-line green', 'runtime' => 'up to 12 h', 'power_source' => 'AA batteries', 'price' => 249.00],
                        ],
                    ],
                    [
                        'brand' => 'hilti',
                        'series' => 'Nuron',
                        'spec' => [
                            'power_source' => '22V Nuron Li-ion',
                        ],
                        'items' => [
                            ['model' => 'PM 30-MG', 'accuracy' => '±0.2 mm/m', 'range' => '45 m', 'self_leveling' => '±4° self-levelling with vibration resistance', 'modes' => 'multi-line green beams', 'runtime' => 'up to 10 h', 'price' => 549.00],
                            ['model' => 'PM 20-CG', 'accuracy' => '±0.3 mm/m', 'range' => '35 m', 'self_leveling' => '±4° auto levelling with lock', 'modes' => 'cross-line green', 'runtime' => 'up to 8 h', 'price' => 489.00],
                            ['model' => 'PR 2-HS A12', 'accuracy' => '±0.5 mm/10 m', 'range' => '600 m with detector', 'self_leveling' => '±5° dual-axis levelling', 'modes' => 'horizontal rotary laser', 'runtime' => 'up to 16 h', 'price' => 899.00],
                        ],
                    ],
                ],
            ],

            'socket-set' => [
                'category_key' => 'hand-tools.wrench-sets',
                'sku_prefix' => 'HT-SS',
                'dimensions' => ['length' => 45.0, 'width' => 30.0, 'height' => 12.0],
                'default_weight' => 6.0,
                'stock' => ['base' => 34, 'step' => 5],
                'type_label' => [
                    'en' => 'socket set',
                    'lt' => 'galvučių rinkinys',
                    'lv' => 'uzgaļu komplekts',
                ],
                'type_display' => [
                    'en' => 'Socket & Wrench Set',
                    'lt' => 'Raktų ir galvučių rinkinys',
                    'lv' => 'Atslēgu un uzgaļu komplekts',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => '{pieces} {type_label} with {drive_size} drive in {case_type}.',
                        'summary' => 'Organised for mechanics with {material} components and secure latches.',
                        'description' => '<p>The {brand} {model} kit keeps sockets sorted for workshop or service-van use.</p><ul><li>{pieces} assortment covering common metric sizes.</li><li>{drive_size} drive with durable {material} construction.</li><li>Stored inside {case_type} for transport and storage.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {pieces}',
                        'seo_description' => 'Equip crews with the {brand} {model} {type_label} featuring {drive_size} drive and {case_type}.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => '{pieces} {type_label} su {drive_size} ir {case_type}.',
                        'summary' => 'Tvarkingas komplektas meistrams iš {material} komponentų su patikimais užraktais.',
                        'description' => '<p>{brand} {model} rinkinys palaiko tvarką dirbtuvėse ar serviso automobilyje.</p><ul><li>{pieces} komplektacija kasdieniams metrinio standarto dydžiams.</li><li>{drive_size} pavara ir patvarus {material} korpusas.</li><li>Laikoma {case_type} patogiam transportavimui.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {pieces}',
                        'seo_description' => 'Aprūpinkite brigadas {brand} {model} {type_label}, turinčiu {drive_size} ir {case_type}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => '{pieces} {type_label} ar {drive_size} un {case_type}.',
                        'summary' => 'Sakārtots komplekts mehāniķiem ar {material} detaļām un drošām aizdarēm.',
                        'description' => '<p>{brand} {model} komplekts uztur kārtību darbnīcā vai servisa busā.</p><ul><li>{pieces} atlase ikdienas metriskajiem izmēriem.</li><li>{drive_size} piedziņa ar izturīgu {material} konstrukciju.</li><li>Glabājas {case_type} ērtai pārvadāšanai.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {pieces}',
                        'seo_description' => 'Nodrošiniet komandas ar {brand} {model} {type_label}, kas ietver {drive_size} un {case_type}.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'ridgid',
                        'series' => 'Pro',
                        'spec' => [
                            'material' => 'Chrome vanadium steel',
                        ],
                        'items' => [
                            ['model' => 'R8600212', 'pieces' => '120-piece', 'drive_size' => '1/4" & 1/2" drive', 'case_type' => 'Sealed steel case', 'material' => 'Chrome vanadium steel', 'weight_kg' => 7.8, 'price' => 329.00],
                            ['model' => 'R8600075', 'pieces' => '75-piece', 'drive_size' => '3/8" drive', 'case_type' => 'Heavy-duty blow mould case', 'material' => 'Polished alloy steel', 'weight_kg' => 6.1, 'price' => 259.00],
                            ['model' => 'R8600050', 'pieces' => '50-piece', 'drive_size' => '1/4" drive', 'case_type' => 'Compact metal latch case', 'material' => 'Chrome vanadium steel', 'weight_kg' => 4.8, 'price' => 199.00],
                        ],
                    ],
                    [
                        'brand' => 'stanley',
                        'series' => 'FatMax',
                        'spec' => [
                            'material' => 'Chrome vanadium steel',
                        ],
                        'items' => [
                            ['model' => 'FMHT1-73607', 'pieces' => '94-piece', 'drive_size' => '1/4" & 1/2" drive', 'case_type' => 'Durable carry case', 'material' => 'Chrome vanadium steel', 'weight_kg' => 5.9, 'price' => 219.00],
                            ['model' => 'STMT82671-1', 'pieces' => '120-piece', 'drive_size' => '1/4" & 3/8" drive', 'case_type' => 'Stackable Tough Case', 'material' => 'Polished steel', 'weight_kg' => 6.4, 'price' => 239.00],
                            ['model' => 'STMT72311', 'pieces' => '75-piece', 'drive_size' => '3/8" drive', 'case_type' => 'Compact carry case', 'material' => 'Chrome vanadium', 'weight_kg' => 4.9, 'price' => 189.00],
                        ],
                    ],
                    [
                        'brand' => 'milwaukee',
                        'series' => 'Packout',
                        'spec' => [
                            'material' => 'Alloy steel',
                        ],
                        'items' => [
                            ['model' => '4932478855', 'pieces' => '106-piece', 'drive_size' => '1/4" & 3/8" drive', 'case_type' => 'Packout slim case', 'material' => 'Stamped alloy steel', 'weight_kg' => 6.7, 'price' => 279.00],
                            ['model' => '4932478857', 'pieces' => '56-piece', 'drive_size' => '1/4" drive', 'case_type' => 'Packout compact case', 'material' => 'Chrome alloy steel', 'weight_kg' => 4.5, 'price' => 219.00],
                            ['model' => '4932492009', 'pieces' => '191-piece', 'drive_size' => '1/4" 3/8" & 1/2" drive', 'case_type' => 'Packout rolling chest', 'material' => 'Chrome alloy steel', 'weight_kg' => 11.5, 'price' => 499.00, 'sale_price' => 469.00],
                        ],
                    ],
                ],
            ],

            'utility-knife' => [
                'category_key' => 'hand-tools.cutting-tools',
                'sku_prefix' => 'HT-UK',
                'dimensions' => ['length' => 18.0, 'width' => 6.0, 'height' => 3.0],
                'default_weight' => 0.4,
                'stock' => ['base' => 60, 'step' => 8],
                'type_label' => [
                    'en' => 'utility knife',
                    'lt' => 'universalus peilis',
                    'lv' => 'universālais nazis',
                ],
                'type_display' => [
                    'en' => 'Utility Knife',
                    'lt' => 'Universalus peilis',
                    'lv' => 'Universālais nazis',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Lightweight {type_label} with {blade_type} blade and {safety}.',
                        'summary' => 'Ergonomic {body_material} handle with {blade_storage} for spare blades.',
                        'description' => '<p>The {brand} {model} tackles drywall, flooring, and packaging.</p><ul><li>{blade_type} blade stays sharp for long cuts.</li><li>{body_material} housing sits secure in the hand.</li><li>{blade_storage} keeps replacements ready with {safety} protection.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {blade_type}',
                        'seo_description' => 'Choose the {brand} {model} {type_label} featuring {body_material} grip and {safety}.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Lengvas {type_label} su {blade_type} geležte ir {safety}.',
                        'summary' => 'Ergonomiška {body_material} rankena su {blade_storage} atsarginėms geležtėms.',
                        'description' => '<p>{brand} {model} tinka gipskartoniui, grindims ir pakuotėms.</p><ul><li>{blade_type} geležtė ilgai išlieka aštri.</li><li>{body_material} korpusas patogiai laikosi rankoje.</li><li>{blade_storage} saugo atsargines geležtes su {safety} apsauga.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {blade_type}',
                        'seo_description' => 'Rinkitės {brand} {model} {type_label} su {body_material} rankena ir {safety}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Viegls {type_label} ar {blade_type} asmeni un {safety}.',
                        'summary' => 'Ergonomisks {body_material} korpuss ar {blade_storage} rezerves asmeņiem.',
                        'description' => '<p>{brand} {model} ir piemērots ģipškartonam, grīdām un iepakojumam.</p><ul><li>{blade_type} asmens nodrošina ilgu griešanu.</li><li>{body_material} korpuss ērti turas rokā.</li><li>{blade_storage} glabā rezerves asmeņus ar {safety} aizsardzību.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {blade_type}',
                        'seo_description' => 'Izvēlieties {brand} {model} {type_label} ar {body_material} korpusu un {safety}.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'stanley',
                        'series' => 'FatMax',
                        'spec' => [
                            'body_material' => 'bi-material grip',
                        ],
                        'items' => [
                            ['model' => 'STHT10432-0', 'blade_type' => 'snap-off 18 mm', 'body_material' => 'bi-material grip', 'blade_storage' => 'holds 6 spare blades', 'safety' => 'auto-lock slider', 'weight_kg' => 0.30, 'price' => 14.90],
                            ['model' => '10-788', 'blade_type' => 'retractable trapezoid', 'body_material' => 'die-cast metal', 'blade_storage' => 'magnetic storage for 5 blades', 'safety' => 'push-button quick change', 'weight_kg' => 0.34, 'price' => 19.50],
                            ['model' => 'FMHT0-10288', 'blade_type' => 'fixed snap-off', 'body_material' => 'reinforced stainless', 'blade_storage' => 'removable cartridge', 'safety' => 'auto-lock segmented', 'weight_kg' => 0.38, 'price' => 24.90],
                        ],
                    ],
                    [
                        'brand' => 'milwaukee',
                        'series' => 'Fastback',
                        'spec' => [
                            'body_material' => 'metal core',
                        ],
                        'items' => [
                            ['model' => '48221911', 'blade_type' => 'self-retracting utility', 'body_material' => 'metal core', 'blade_storage' => 'stores 5 blades', 'safety' => 'self-retracting mechanism', 'weight_kg' => 0.36, 'price' => 27.90],
                            ['model' => '48221910', 'blade_type' => 'fastback folding', 'body_material' => 'aluminium handle', 'blade_storage' => 'extra blade storage', 'safety' => 'press-and-flip release', 'weight_kg' => 0.32, 'price' => 21.90],
                            ['model' => '48221870', 'blade_type' => 'snap-off 25 mm', 'body_material' => 'glass-filled nylon', 'blade_storage' => 'integrated snap-off chamber', 'safety' => 'anti-slip slider', 'weight_kg' => 0.29, 'price' => 18.90],
                        ],
                    ],
                    [
                        'brand' => 'dewalt',
                        'series' => 'XR',
                        'spec' => [
                            'body_material' => 'aluminium body',
                        ],
                        'items' => [
                            ['model' => 'DWHT10046', 'blade_type' => 'folding utility', 'body_material' => 'aluminium body', 'blade_storage' => 'stores 4 blades', 'safety' => 'locking folding handle', 'weight_kg' => 0.33, 'price' => 24.50],
                            ['model' => 'DWHT10295', 'blade_type' => 'carbide utility', 'body_material' => 'stainless nose', 'blade_storage' => 'rear magazine', 'safety' => 'push-button quick change', 'weight_kg' => 0.31, 'price' => 27.50],
                            ['model' => 'DWHT10038', 'blade_type' => 'retractable snap-off', 'body_material' => 'composite grip', 'blade_storage' => 'side-access magazine', 'safety' => 'auto-locking slider', 'weight_kg' => 0.28, 'price' => 17.90],
                        ],
                    ],
                ],
            ],

            'safety-helmet' => [
                'category_key' => 'safety-equipment.protective-gear',
                'sku_prefix' => 'SE-SH',
                'dimensions' => ['length' => 30.0, 'width' => 24.0, 'height' => 20.0],
                'default_weight' => 0.6,
                'stock' => ['base' => 80, 'step' => 10],
                'type_label' => [
                    'en' => 'safety helmet',
                    'lt' => 'apsauginis šalmas',
                    'lv' => 'drošības ķivere',
                ],
                'type_display' => [
                    'en' => 'Safety Helmet',
                    'lt' => 'Apsauginis šalmas',
                    'lv' => 'Drošības ķivere',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Certified {type_label} meeting {rating} with {suspension}.',
                        'summary' => 'Comfortable shell includes {accessories} for jobsite integration.',
                        'description' => '<p>Keep crews compliant and protected with the {brand} {model}.</p><ul><li>{rating} certification for Baltic construction and industry.</li><li>{suspension} delivers all-day comfort.</li><li>{accessories} expand compatibility with face and hearing protection.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {rating}',
                        'seo_description' => 'Protect teams with the {brand} {model} {type_label} featuring {suspension} and {accessories}.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Sertifikuotas {type_label}, atitinkantis {rating}, su {suspension}.',
                        'summary' => 'Patogus korpusas su {accessories}, pritaikytas darbų aikštelėms.',
                        'description' => '<p>Apsaugokite brigadas su {brand} {model}.</p><ul><li>{rating} sertifikatas Baltijos statyboms ir pramonei.</li><li>{suspension} užtikrina komfortą visą dieną.</li><li>{accessories} leidžia derinti su veido ir klausos apsauga.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {rating}',
                        'seo_description' => 'Pasirinkite {brand} {model} {type_label} su {suspension} ir {accessories}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Sertificēta {type_label}, kas atbilst {rating}, ar {suspension}.',
                        'summary' => 'Ērta čaula ar {accessories}, pielāgota būvlaukumiem.',
                        'description' => '<p>Aizsargājiet komandas ar {brand} {model}.</p><ul><li>{rating} sertifikācija Baltijas būvniecībai un industrijai.</li><li>{suspension} nodrošina komfortu visas dienas garumā.</li><li>{accessories} ļauj kombinēt ar sejas un dzirdes aizsardzību.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {rating}',
                        'seo_description' => 'Izvēlieties {brand} {model} {type_label} ar {suspension} un {accessories}.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => '3m',
                        'series' => 'SecureFit',
                        'spec' => [
                            'rating' => 'EN 397',
                        ],
                        'items' => [
                            ['model' => 'H-700V-GU', 'rating' => 'EN 397', 'suspension' => '4-point ratchet suspension', 'accessories' => 'vented shell with Uvicator', 'weight_kg' => 0.45, 'price' => 39.90],
                            ['model' => 'H-804N', 'rating' => 'EN 12492', 'suspension' => 'six-point textile harness', 'accessories' => 'integrated chinstrap and earmuff slots', 'weight_kg' => 0.52, 'price' => 59.90],
                            ['model' => 'G3000NUV', 'rating' => 'EN 397', 'suspension' => 'ratchet harness', 'accessories' => 'rain gutter and reflective decals', 'weight_kg' => 0.43, 'price' => 44.90],
                        ],
                    ],
                    [
                        'brand' => 'uvex',
                        'series' => 'Pheos',
                        'spec' => [
                            'rating' => 'EN 397',
                        ],
                        'items' => [
                            ['model' => 'Pheos S-KR', 'rating' => 'EN 397', 'suspension' => 'climate-regulating 3D harness', 'accessories' => 'slots for visors and ear defenders', 'weight_kg' => 0.46, 'price' => 48.50],
                            ['model' => 'Airwing B-WR', 'rating' => 'EN 50365', 'suspension' => '4-point wheel ratchet', 'accessories' => 'ventilation channels and chinstrap', 'weight_kg' => 0.41, 'price' => 42.00],
                            ['model' => 'Perfexxion', 'rating' => 'EN 12492', 'suspension' => 'adjustable IAS system', 'accessories' => 'lamp mount and hearing protection adapter', 'weight_kg' => 0.49, 'price' => 69.00],
                        ],
                    ],
                    [
                        'brand' => 'honeywell',
                        'series' => 'North Zone',
                        'spec' => [
                            'rating' => 'EN 397',
                        ],
                        'items' => [
                            ['model' => 'V-Gard 520', 'rating' => 'EN 397', 'suspension' => 'Fas-Trac III ratchet', 'accessories' => 'slots for V-Gard visors', 'weight_kg' => 0.44, 'price' => 37.50],
                            ['model' => 'North Zone 2', 'rating' => 'EN 397', 'suspension' => '4-point pinlock harness', 'accessories' => 'sweatband and accessory slots', 'weight_kg' => 0.47, 'price' => 34.90],
                            ['model' => 'Fibre-Metal P2ARW', 'rating' => 'EN 397 & ANSI Z89.1', 'suspension' => 'SuperEight ratchet', 'accessories' => 'full brim with welding shield mounts', 'weight_kg' => 0.53, 'price' => 54.00],
                        ],
                    ],
                ],
            ],

            'respirator' => [
                'category_key' => 'safety-equipment.respiratory-protection',
                'sku_prefix' => 'SE-RP',
                'dimensions' => ['length' => 28.0, 'width' => 20.0, 'height' => 15.0],
                'default_weight' => 0.8,
                'stock' => ['base' => 75, 'step' => 8],
                'type_label' => [
                    'en' => 'respirator',
                    'lt' => 'respiratorius',
                    'lv' => 'respirators',
                ],
                'type_display' => [
                    'en' => 'Reusable Respirator',
                    'lt' => 'Daugkartinis respiratorius',
                    'lv' => 'Atkārtoti lietojams respirators',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Reusable {type_label} with {filters} and {valve}.',
                        'summary' => 'Secure fit using {straps} to keep protection consistent all shift.',
                        'description' => '<p>The {brand} {model} defends against dust and aerosols.</p><ul><li>{filters} provide certified filtration performance.</li><li>{valve} reduces breathing resistance.</li><li>{straps} maintain a reliable seal for active crews.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {filters}',
                        'seo_description' => 'Protect breathing zones with the {brand} {model} {type_label} featuring {filters} and {valve}.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Daugkartinis {type_label} su {filters} ir {valve}.',
                        'summary' => 'Tvirtas prigludimas su {straps}, išliekantis visą pamainą.',
                        'description' => '<p>{brand} {model} saugo nuo dulkių ir aerozolių.</p><ul><li>{filters} užtikrina sertifikuotą filtraciją.</li><li>{valve} sumažina kvėpavimo pasipriešinimą.</li><li>{straps} palaiko sandarumą aktyvioms brigadoms.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {filters}',
                        'seo_description' => 'Apsaugokite kvėpavimą su {brand} {model} {type_label}, turinčiu {filters} ir {valve}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Atkārtoti lietojams {type_label} ar {filters} un {valve}.',
                        'summary' => 'Droša piegulēšana ar {straps}, kas saglabā aizsardzību visas maiņas garumā.',
                        'description' => '<p>{brand} {model} aizsargā pret putekļiem un aerosoliem.</p><ul><li>{filters} nodrošina sertificētu filtrāciju.</li><li>{valve} samazina elpošanas pretestību.</li><li>{straps} uztur uzticamu blīvējumu aktīvām komandām.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {filters}',
                        'seo_description' => 'Sargājiet elpceļus ar {brand} {model} {type_label}, kuram ir {filters} un {valve}.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => '3m',
                        'series' => 'Half Mask',
                        'spec' => [
                            'filters' => 'P3 bayonet filters',
                        ],
                        'items' => [
                            ['model' => '6502QL', 'filters' => 'P3 R bayonet filters', 'valve' => '3M Cool Flow valve', 'straps' => 'Quick Latch head harness', 'weight_kg' => 0.36, 'price' => 42.90],
                            ['model' => '7502', 'filters' => 'bayonet P2/P3 cartridges', 'valve' => 'low-resistance exhalation valve', 'straps' => 'soft silicone twin strap', 'weight_kg' => 0.37, 'price' => 39.90],
                            ['model' => 'FF-803X4', 'filters' => 'FF-400 series cartridges', 'valve' => 'Cool Flow exhalation valve', 'straps' => 'silicone head cradle', 'weight_kg' => 0.44, 'price' => 59.90],
                        ],
                    ],
                    [
                        'brand' => 'honeywell',
                        'series' => 'North',
                        'spec' => [
                            'filters' => 'P3 cartridges',
                        ],
                        'items' => [
                            ['model' => 'North 7700', 'filters' => 'P3 bayonet filters', 'valve' => 'front exhalation valve', 'straps' => 'dual-elastic headband', 'weight_kg' => 0.35, 'price' => 38.50],
                            ['model' => 'RU8500X', 'filters' => 'threaded P3 cartridges', 'valve' => 'speech diaphragm valve', 'straps' => 'silicone cradle straps', 'weight_kg' => 0.45, 'price' => 54.00],
                            ['model' => 'T8000', 'filters' => 'A2P3 cartridges', 'valve' => 'moisture barrier valve', 'straps' => '5-point harness', 'weight_kg' => 0.48, 'price' => 62.00],
                        ],
                    ],
                    [
                        'brand' => 'uvex',
                        'series' => 'silv-Air',
                        'spec' => [
                            'filters' => 'FFP replaceable filters',
                        ],
                        'items' => [
                            ['model' => 'Silv-Air 3310', 'filters' => 'FFP3 replaceable filters', 'valve' => 'low resistance valve', 'straps' => 'flexible textile headband', 'weight_kg' => 0.28, 'price' => 24.90],
                            ['model' => 'Silv-Air 7310', 'filters' => 'FFP2 filter set', 'valve' => '360° breathing valve', 'straps' => 'adjustable ear loops', 'weight_kg' => 0.26, 'price' => 19.90],
                            ['model' => 'Silv-Air e 7000', 'filters' => 'reusable P3 cartridges', 'valve' => 'optimised exhalation valve', 'straps' => 'soft sealing straps', 'weight_kg' => 0.31, 'price' => 34.90],
                        ],
                    ],
                ],
            ],

            'safety-boot' => [
                'category_key' => 'safety-equipment.footwear',
                'sku_prefix' => 'SE-SB',
                'dimensions' => ['length' => 38.0, 'width' => 28.0, 'height' => 26.0],
                'default_weight' => 1.8,
                'stock' => ['base' => 90, 'step' => 12],
                'type_label' => [
                    'en' => 'safety boot',
                    'lt' => 'apsauginis batas',
                    'lv' => 'drošības zābaks',
                ],
                'type_display' => [
                    'en' => 'Safety Boot',
                    'lt' => 'Apsauginė avalynė',
                    'lv' => 'Drošības apavi',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Water-resistant {type_label} rated {rating} with {upper_material} upper.',
                        'summary' => 'Shock-absorbing {outsole} and {lining} keep crews comfortable.',
                        'description' => '<p>The {brand} {model} boot endures rugged Baltic job sites.</p><ul><li>{rating} protection meets toe-cap and puncture standards.</li><li>{upper_material} combines durability with weather resistance.</li><li>{outsole} paired with {lining} keeps footing secure and feet warm.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {rating}',
                        'seo_description' => 'Equip crews with {brand} {model} {type_label} using {upper_material} and {outsole}.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Atsparūs vandeniui {type_label} su {upper_material} ir {rating}.',
                        'summary' => 'Smūgius sugeriantis {outsole} ir {lining} užtikrina komfortą.',
                        'description' => '<p>{brand} {model} batai atlaiko sudėtingas Baltijos sąlygas.</p><ul><li>{rating} apsauga atitinka pirštų ir pradūrimo standartus.</li><li>{upper_material} užtikrina patvarumą ir atsparumą orams.</li><li>{outsole} ir {lining} palaiko stabilumą ir šilumą.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {rating}',
                        'seo_description' => 'Aprūpinkite brigadas {brand} {model} {type_label} su {upper_material} ir {outsole}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Ūdensizturīgi {type_label} ar {upper_material} un {rating}.',
                        'summary' => 'Triecienu absorbējošs {outsole} un {lining} sniedz komfortu.',
                        'description' => '<p>{brand} {model} apavi iztur skarbus Baltijas objektus.</p><ul><li>{rating} aizsardzība atbilst purngalu un caurduršanas prasībām.</li><li>{upper_material} nodrošina izturību un aizsardzību pret laikapstākļiem.</li><li>{outsole} kopā ar {lining} nodrošina drošu saķeri un siltumu.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {rating}',
                        'seo_description' => 'Nodrošiniet komandas ar {brand} {model} {type_label}, kas izmanto {upper_material} un {outsole}.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'uvex',
                        'series' => '1 G2',
                        'spec' => [
                            'rating' => 'S3',
                        ],
                        'items' => [
                            ['model' => 'Uvex 1 G2', 'rating' => 'S3', 'upper_material' => 'micro-velour upper', 'outsole' => 'PU/i-PU sole', 'lining' => 'climate control lining', 'weight_kg' => 1.12, 'price' => 109.00],
                            ['model' => 'Uvex Heckel MacCross', 'rating' => 'S5', 'upper_material' => 'polyurethane boot', 'outsole' => 'self-cleaning PU sole', 'lining' => 'textile lining', 'weight_kg' => 1.45, 'price' => 89.00],
                            ['model' => 'Uvex 2 xenova', 'rating' => 'S1P', 'upper_material' => 'perforated microfibre', 'outsole' => 'xenova PU/TPU sole', 'lining' => 'distance mesh lining', 'weight_kg' => 1.05, 'price' => 99.00],
                        ],
                    ],
                    [
                        'brand' => 'honeywell',
                        'series' => 'Safety Footwear',
                        'spec' => [
                            'rating' => 'S3',
                        ],
                        'items' => [
                            ['model' => 'Honeywell Talon 3', 'rating' => 'S3', 'upper_material' => 'full-grain leather', 'outsole' => 'dual-density PU', 'lining' => 'moisture-wicking textile', 'weight_kg' => 1.28, 'price' => 94.50],
                            ['model' => 'Honeywell Tractel S5', 'rating' => 'S5', 'upper_material' => 'PVC upper', 'outsole' => 'deep cleat PVC outsole', 'lining' => 'removable textile', 'weight_kg' => 1.52, 'price' => 74.00],
                            ['model' => 'Honeywell Phoenix', 'rating' => 'S1P', 'upper_material' => 'suede and mesh', 'outsole' => 'PU/TPU slip-resistant', 'lining' => 'breathable mesh', 'weight_kg' => 1.18, 'price' => 82.00],
                        ],
                    ],
                    [
                        'brand' => 'dewalt',
                        'series' => 'Work Boots',
                        'spec' => [
                            'rating' => 'S3',
                        ],
                        'items' => [
                            ['model' => 'DeWalt Newark Pro', 'rating' => 'S3', 'upper_material' => 'nubuck leather', 'outsole' => 'rubber anti-slip outsole', 'lining' => 'padded mesh lining', 'weight_kg' => 1.24, 'price' => 104.00],
                            ['model' => 'DeWalt Krypton', 'rating' => 'S1P', 'upper_material' => 'synthetic mesh upper', 'outsole' => 'dual density sole', 'lining' => 'absorbent inner lining', 'weight_kg' => 1.10, 'price' => 92.00],
                            ['model' => 'DeWalt Extreme 3', 'rating' => 'S3 WR', 'upper_material' => 'waterproof leather', 'outsole' => 'heat-resistant rubber sole', 'lining' => 'Thinsulate lining', 'weight_kg' => 1.36, 'price' => 119.00, 'sale_price' => 112.00],
                        ],
                    ],
                ],
            ],

            'extension-cord' => [
                'category_key' => 'electrical-lighting.cabling',
                'sku_prefix' => 'EL-EC',
                'dimensions' => ['length' => 40.0, 'width' => 32.0, 'height' => 18.0],
                'default_weight' => 4.0,
                'stock' => ['base' => 70, 'step' => 6],
                'type_label' => [
                    'en' => 'extension cord',
                    'lt' => 'prailgintuvas',
                    'lv' => 'pagarinātājs',
                ],
                'type_display' => [
                    'en' => 'Extension Cord Reel',
                    'lt' => 'Prailgintuvo ritė',
                    'lv' => 'Pagarinātāja spole',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Heavy-duty {length} {type_label} with {outlets} and {ip_rating} rating.',
                        'summary' => 'Flexible {gauge} cable with thermal protection for demanding sites.',
                        'description' => '<p>Distribute temporary power safely with the {brand} {model} reel.</p><ul><li>{length} reach built with {gauge} cable.</li><li>{outlets} ready for jobsite equipment.</li><li>{ip_rating} ingress rating keeps moisture and dust out.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {length}',
                        'seo_description' => 'Extend power with the {brand} {model} {type_label} offering {outlets} and {ip_rating} protection.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Tvirtas {length} {type_label} su {outlets} ir {ip_rating} apsauga.',
                        'summary' => 'Lankstus {gauge} kabelis su terminiu saugikliu sudėtingoms sąlygoms.',
                        'description' => '<p>Saugiai paskirstykite laikiną energiją su {brand} {model} ritė.</p><ul><li>{length} ilgio {gauge} kabelis.</li><li>{outlets} pritaikytos statybvietės įrangai.</li><li>{ip_rating} apsauga nuo drėgmės ir dulkių.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {length}',
                        'seo_description' => 'Išplėskite maitinimą naudodami {brand} {model} {type_label} su {outlets} ir {ip_rating} apsauga.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Izturīgs {length} {type_label} ar {outlets} un {ip_rating} aizsardzību.',
                        'summary' => 'Elastīgs {gauge} kabelis ar termisko aizsardzību prasīgiem objektiem.',
                        'description' => '<p>Droši sadaliet pagaidu elektroapgādi ar {brand} {model} spoli.</p><ul><li>{length} garums ar {gauge} kabeli.</li><li>{outlets} der aprīkojumam būvlaukumā.</li><li>{ip_rating} aizsardzība pret mitrumu un putekļiem.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {length}',
                        'seo_description' => 'Paplašiniet elektroapgādi ar {brand} {model} {type_label}, kas nodrošina {outlets} un {ip_rating} aizsardzību.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'legrand',
                        'series' => 'Professional',
                        'spec' => [
                            'gauge' => 'H07RN-F 3G2.5',
                        ],
                        'items' => [
                            ['model' => '044879', 'length' => '25 m', 'gauge' => 'H07RN-F 3G2.5', 'outlets' => '4 schuko sockets', 'ip_rating' => 'IP44', 'weight_kg' => 4.6, 'price' => 129.00],
                            ['model' => '050283', 'length' => '40 m', 'gauge' => 'H07RN-F 3G1.5', 'outlets' => '3 schuko sockets', 'ip_rating' => 'IP44', 'weight_kg' => 5.2, 'price' => 139.00],
                            ['model' => '036761', 'length' => '15 m', 'gauge' => 'H05RR-F 3G1.5', 'outlets' => '2 schuko + 1 CEE', 'ip_rating' => 'IP44', 'weight_kg' => 3.8, 'price' => 109.00],
                        ],
                    ],
                    [
                        'brand' => 'stanley',
                        'series' => 'FatMax',
                        'spec' => [
                            'gauge' => 'H07RN-F 3G2.5',
                        ],
                        'items' => [
                            ['model' => 'STMT89983', 'length' => '30 m', 'gauge' => 'H07RN-F 3G2.5', 'outlets' => '4 outlets with covers', 'ip_rating' => 'IP54', 'weight_kg' => 4.9, 'price' => 119.00],
                            ['model' => 'STHT0-51352', 'length' => '20 m', 'gauge' => 'H05VV-F 3G1.5', 'outlets' => '3 outlets + thermal breaker', 'ip_rating' => 'IP20', 'weight_kg' => 3.2, 'price' => 79.00],
                            ['model' => 'STST1-72335', 'length' => '50 m', 'gauge' => 'H07RN-F 3G2.5', 'outlets' => '4 industrial sockets', 'ip_rating' => 'IP44', 'weight_kg' => 6.1, 'price' => 159.00],
                        ],
                    ],
                    [
                        'brand' => 'milwaukee',
                        'series' => 'Packout Power',
                        'spec' => [
                            'gauge' => 'Rubber 3G2.5',
                        ],
                        'items' => [
                            ['model' => '4932464512', 'length' => '25 m', 'gauge' => 'Rubber 3G2.5', 'outlets' => '4 Packout-ready sockets', 'ip_rating' => 'IP54', 'weight_kg' => 4.4, 'price' => 139.00],
                            ['model' => '4932464511', 'length' => '15 m', 'gauge' => 'Rubber 3G1.5', 'outlets' => '2 sockets + USB', 'ip_rating' => 'IP44', 'weight_kg' => 3.5, 'price' => 119.00],
                            ['model' => '4932464078', 'length' => '35 m', 'gauge' => 'H07RN-F 3G2.5', 'outlets' => '4 sockets with LED indicators', 'ip_rating' => 'IP54', 'weight_kg' => 5.4, 'price' => 149.00],
                        ],
                    ],
                ],
            ],

            'led-worklight' => [
                'category_key' => 'electrical-lighting.work-lighting',
                'sku_prefix' => 'EL-WL',
                'dimensions' => ['length' => 32.0, 'width' => 25.0, 'height' => 35.0],
                'default_weight' => 3.5,
                'stock' => ['base' => 60, 'step' => 7],
                'type_label' => [
                    'en' => 'work light',
                    'lt' => 'darbo šviestuvas',
                    'lv' => 'darba gaisma',
                ],
                'type_display' => [
                    'en' => 'LED Work Light',
                    'lt' => 'LED darbo šviestuvas',
                    'lv' => 'LED darba gaisma',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Portable {type_label} delivering {lumens} with {modes} modes.',
                        'summary' => 'Runs on {power_source} with up to {runtime} runtime and {color_temp} output.',
                        'description' => '<p>Illuminate fit-out and service work with the {brand} {model}.</p><ul><li>{lumens} brightness keeps areas visible.</li><li>{modes} adapt to the task.</li><li>Powered by {power_source} for {runtime} of operation.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {lumens}',
                        'seo_description' => 'Light jobsites using the {brand} {model} {type_label} with {lumens} output and {runtime} runtime.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Nešiojamas {type_label}, skleidžiantis {lumens} su {modes} režimais.',
                        'summary' => 'Veikia su {power_source} iki {runtime}, užtikrinant {color_temp} šviesą.',
                        'description' => '<p>Apšvieskite apdailos ir aptarnavimo darbus su {brand} {model}.</p><ul><li>{lumens} ryškumas užtikrina matomumą.</li><li>{modes} prisitaiko prie užduoties.</li><li>{power_source} leidžia dirbti iki {runtime}.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {lumens}',
                        'seo_description' => 'Apšvieskite objektus su {brand} {model} {type_label}, kuris veikia {runtime} ir skleidžia {color_temp}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Pārnēsājama {type_label}, kas nodrošina {lumens} ar {modes} režīmiem.',
                        'summary' => 'Darbojas ar {power_source} līdz {runtime}, nodrošinot {color_temp} gaismu.',
                        'description' => '<p>Apgaismojiet montāžas un servisa darbus ar {brand} {model}.</p><ul><li>{lumens} nodrošina izcilu redzamību.</li><li>{modes} pielāgojas uzdevumam.</li><li>{power_source} nodrošina darbu līdz {runtime}.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {lumens}',
                        'seo_description' => 'Apgaismojiet objektus ar {brand} {model} {type_label}, kas darbojas {runtime} un sniedz {color_temp} gaismu.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'milwaukee',
                        'series' => 'M18',
                        'spec' => [
                            'power_source' => 'M18 battery',
                        ],
                        'items' => [
                            ['model' => 'M18 SAL-0', 'lumens' => '12,000 lm', 'modes' => 'high/medium/low', 'runtime' => 'up to 12 h', 'power_source' => 'M18 battery or AC', 'color_temp' => '4000K', 'weight_kg' => 7.0, 'price' => 359.00],
                            ['model' => 'M18 TAL-0', 'lumens' => '2,200 lm', 'modes' => 'task/flood', 'runtime' => 'up to 14 h', 'power_source' => 'M18 battery', 'color_temp' => '5000K', 'weight_kg' => 3.2, 'price' => 199.00],
                            ['model' => 'M12 AL-0', 'lumens' => '1,000 lm', 'modes' => 'low/high', 'runtime' => 'up to 8 h', 'power_source' => 'M12 battery', 'color_temp' => '4000K', 'weight_kg' => 1.5, 'price' => 129.00],
                        ],
                    ],
                    [
                        'brand' => 'dewalt',
                        'series' => 'XR',
                        'spec' => [
                            'power_source' => '18V XR battery',
                        ],
                        'items' => [
                            ['model' => 'DCL079', 'lumens' => '3,000 lm', 'modes' => 'high/medium/low', 'runtime' => 'up to 10 h', 'power_source' => '18V XR battery', 'color_temp' => '5000K', 'weight_kg' => 4.8, 'price' => 259.00],
                            ['model' => 'DCL077', 'lumens' => '2,000 lm', 'modes' => 'spot/flood', 'runtime' => 'up to 7 h', 'power_source' => '18V XR battery', 'color_temp' => '4000K', 'weight_kg' => 3.1, 'price' => 179.00],
                            ['model' => 'DCL074', 'lumens' => '3,600 lm', 'modes' => '360° area', 'runtime' => 'up to 5 h', 'power_source' => '54V FlexVolt', 'color_temp' => '4500K', 'weight_kg' => 4.5, 'price' => 299.00],
                        ],
                    ],
                    [
                        'brand' => 'philips',
                        'series' => 'ClearFlood',
                        'spec' => [
                            'power_source' => '230V AC',
                        ],
                        'items' => [
                            ['model' => 'ClearFlood 50', 'lumens' => '6,000 lm', 'modes' => 'single brightness', 'runtime' => 'continuous', 'power_source' => '230V AC', 'color_temp' => '4000K', 'weight_kg' => 5.6, 'price' => 189.00],
                            ['model' => 'Ledinaire BVP164', 'lumens' => '10,000 lm', 'modes' => 'single brightness', 'runtime' => 'continuous', 'power_source' => '230V AC', 'color_temp' => '5000K', 'weight_kg' => 6.2, 'price' => 229.00],
                            ['model' => 'WT120C', 'lumens' => '4,200 lm', 'modes' => 'two-step brightness', 'runtime' => 'continuous', 'power_source' => '230V AC', 'color_temp' => '6500K', 'weight_kg' => 4.1, 'price' => 169.00],
                        ],
                    ],
                ],
            ],

            'string-trimmer' => [
                'category_key' => 'outdoor-garden.outdoor-power',
                'sku_prefix' => 'OG-ST',
                'dimensions' => ['length' => 165.0, 'width' => 25.0, 'height' => 25.0],
                'default_weight' => 4.2,
                'stock' => ['base' => 45, 'step' => 5],
                'type_label' => [
                    'en' => 'string trimmer',
                    'lt' => 'žoliapjovė',
                    'lv' => 'zāles trimmeris',
                ],
                'type_display' => [
                    'en' => 'String Trimmer',
                    'lt' => 'Akumuliatorinė žoliapjovė',
                    'lv' => 'Akumulatora zāles trimmeris',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Cordless {type_label} with {cutting_width} cutting width and {line_diameter} line.',
                        'summary' => 'Efficient {motor_type} paired with {power_source} runs up to {runtime}.',
                        'description' => '<p>Maintain landscaping edges with the {brand} {model}.</p><ul><li>{cutting_width} swath clears grass quickly.</li><li>{line_diameter} cutting line feeds smoothly.</li><li>{motor_type} powered by {power_source} delivers {runtime} runtime.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {cutting_width}',
                        'seo_description' => 'Trim with the {brand} {model} {type_label} offering {cutting_width} width and {runtime} runtime.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Akumuliatorinė {type_label} su {cutting_width} pjovimo pločiu ir {line_diameter} valu.',
                        'summary' => 'Efektyvus {motor_type} su {power_source} veikia iki {runtime}.',
                        'description' => '<p>Prižiūrėkite veją su {brand} {model}.</p><ul><li>{cutting_width} plotis greitai nušienauja kraštus.</li><li>{line_diameter} valas patikimai paduodamas.</li><li>{motor_type}, maitinamas {power_source}, užtikrina {runtime} darbą.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {cutting_width}',
                        'seo_description' => 'Naudokite {brand} {model} {type_label} su {cutting_width} ir {runtime} veikimu.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Akumulatora {type_label} ar {cutting_width} pļaušanas platumu un {line_diameter} auklu.',
                        'summary' => 'Efektīvs {motor_type} un {power_source} darbojas līdz {runtime}.',
                        'description' => '<p>Kopiet ainavu ar {brand} {model}.</p><ul><li>{cutting_width} josla ātri apstrādā malas.</li><li>{line_diameter} aukla uztur stabilu griešanu.</li><li>{motor_type}, ko darbina {power_source}, nodrošina {runtime} darbu.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {cutting_width}',
                        'seo_description' => 'Izvēlieties {brand} {model} {type_label} ar {cutting_width} un {runtime} darbību.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'stihl',
                        'series' => 'FSA',
                        'spec' => [
                            'power_source' => 'AP battery system',
                        ],
                        'items' => [
                            ['model' => 'FSA 135 R', 'cutting_width' => '420 mm', 'line_diameter' => '2.4 mm', 'motor_type' => 'brushless EC motor', 'power_source' => 'AP battery system', 'runtime' => 'up to 45 min', 'weight_kg' => 4.9, 'price' => 449.00],
                            ['model' => 'FSA 57', 'cutting_width' => '280 mm', 'line_diameter' => '1.6 mm', 'motor_type' => 'brushless EC motor', 'power_source' => 'AK battery', 'runtime' => 'up to 25 min', 'weight_kg' => 3.5, 'price' => 229.00],
                            ['model' => 'FSA 86 R', 'cutting_width' => '350 mm', 'line_diameter' => '2.0 mm', 'motor_type' => 'brushless EC motor', 'power_source' => 'AP battery system', 'runtime' => 'up to 30 min', 'weight_kg' => 3.3, 'price' => 319.00],
                        ],
                    ],
                    [
                        'brand' => 'husqvarna',
                        'series' => 'Battery',
                        'spec' => [
                            'power_source' => '36V battery',
                        ],
                        'items' => [
                            ['model' => '520iLX', 'cutting_width' => '400 mm', 'line_diameter' => '2.4 mm', 'motor_type' => 'brushless motor', 'power_source' => 'BLi battery', 'runtime' => 'up to 35 min', 'weight_kg' => 3.0, 'price' => 399.00],
                            ['model' => '325iLK', 'cutting_width' => '420 mm', 'line_diameter' => '2.7 mm', 'motor_type' => 'brushless multi-tool motor', 'power_source' => 'BLi battery', 'runtime' => 'up to 40 min', 'weight_kg' => 4.1, 'price' => 469.00],
                            ['model' => '215iL', 'cutting_width' => '330 mm', 'line_diameter' => '2.0 mm', 'motor_type' => 'brushless motor', 'power_source' => 'Power for All battery', 'runtime' => 'up to 30 min', 'weight_kg' => 3.2, 'price' => 269.00],
                        ],
                    ],
                    [
                        'brand' => 'einhell',
                        'series' => 'Power X-Change',
                        'spec' => [
                            'power_source' => 'Power X-Change battery',
                        ],
                        'items' => [
                            ['model' => 'GE-CT 36/30 Li', 'cutting_width' => '300 mm', 'line_diameter' => '2.0 mm', 'motor_type' => 'brushless motor', 'power_source' => '36V Power X-Change', 'runtime' => 'up to 40 min', 'weight_kg' => 3.6, 'price' => 199.00],
                            ['model' => 'Agillo 18/200', 'cutting_width' => '300 mm', 'line_diameter' => '2.4 mm', 'motor_type' => 'front motor', 'power_source' => '18V Power X-Change', 'runtime' => 'up to 30 min', 'weight_kg' => 3.8, 'price' => 219.00],
                            ['model' => 'GC-CT 18/24 Li', 'cutting_width' => '240 mm', 'line_diameter' => '1.6 mm', 'motor_type' => 'high-torque motor', 'power_source' => '18V Power X-Change', 'runtime' => 'up to 25 min', 'weight_kg' => 2.4, 'price' => 119.00],
                        ],
                    ],
                ],
            ],

            'pressure-washer' => [
                'category_key' => 'outdoor-garden.water-management',
                'sku_prefix' => 'OG-PW',
                'dimensions' => ['length' => 85.0, 'width' => 35.0, 'height' => 40.0],
                'default_weight' => 17.0,
                'stock' => ['base' => 30, 'step' => 4],
                'type_label' => [
                    'en' => 'pressure washer',
                    'lt' => 'aukšto slėgio plovykla',
                    'lv' => 'augstspiediena mazgātājs',
                ],
                'type_display' => [
                    'en' => 'Pressure Washer',
                    'lt' => 'Aukšto slėgio plovykla',
                    'lv' => 'Augstspiediena mazgātājs',
                ],
                'templates' => [
                    'en' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Professional {type_label} delivering {pressure} with {flow_rate} flow.',
                        'summary' => 'Durable {motor_type} paired with {hose_length} and {accessories}.',
                        'description' => '<p>Deep clean equipment and surfaces with the {brand} {model}.</p><ul><li>{pressure} pressure tackles concrete and heavy dirt.</li><li>{flow_rate} flow keeps rinsing efficient.</li><li>{motor_type} supports continuous use with {hose_length} and {accessories} included.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {pressure}',
                        'seo_description' => 'Upgrade washing capacity with the {brand} {model} {type_label} providing {pressure} and {flow_rate}.',
                    ],
                    'lt' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Profesionali {type_label}, tiekianti {pressure} ir {flow_rate}.',
                        'summary' => 'Patvarus {motor_type} su {hose_length} ir {accessories}.',
                        'description' => '<p>Valykite įrangą ir dangas su {brand} {model}.</p><ul><li>{pressure} slėgis įveikia betoną ir sunkias nešvaras.</li><li>{flow_rate} srautas efektyviai nuplauna.</li><li>{motor_type} palaiko nuolatinį darbą, komplektuojama su {hose_length} ir {accessories}.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {pressure}',
                        'seo_description' => 'Pagerinkite plovimo galimybes su {brand} {model} {type_label}, kuri pasižymi {pressure} ir {flow_rate}.',
                    ],
                    'lv' => [
                        'name' => '{brand} {model} {series_label}{type_display}',
                        'short_description' => 'Profesionāls {type_label} ar {pressure} un {flow_rate} plūsmu.',
                        'summary' => 'Izturīgs {motor_type} ar {hose_length} un {accessories}.',
                        'description' => '<p>Tīriet aprīkojumu un virsmas ar {brand} {model}.</p><ul><li>{pressure} spiediens tiek galā ar betonu un smagiem netīrumiem.</li><li>{flow_rate} plūsma padara skalošanu efektīvu.</li><li>{motor_type} nodrošina nepārtrauktu darbu ar {hose_length} un {accessories} komplektā.</li></ul>',
                        'seo_title' => '{brand} {model} {type_display} – {pressure}',
                        'seo_description' => 'Uzlabojiet mazgāšanu ar {brand} {model} {type_label}, kas nodrošina {pressure} un {flow_rate}.',
                    ],
                ],
                'models' => [
                    [
                        'brand' => 'karcher',
                        'series' => 'HD',
                        'spec' => [
                            'motor_type' => 'induction motor',
                        ],
                        'items' => [
                            ['model' => 'HD 6/15 MXA', 'pressure' => '150 bar', 'flow_rate' => '560 l/h', 'motor_type' => 'induction motor', 'hose_length' => '15 m hose reel', 'accessories' => 'Easy!Force gun and spray lance', 'weight_kg' => 28.0, 'price' => 729.00],
                            ['model' => 'HD 5/17 CX Plus', 'pressure' => '170 bar', 'flow_rate' => '500 l/h', 'motor_type' => 'three-piston axial pump', 'hose_length' => '15 m high-pressure hose', 'accessories' => 'Auto-stop gun and detergent injector', 'weight_kg' => 26.0, 'price' => 679.00],
                            ['model' => 'HD 4/11 C Bp', 'pressure' => '110 bar', 'flow_rate' => '400 l/h', 'motor_type' => 'battery-powered brushless', 'hose_length' => '10 m hose', 'accessories' => 'Dual battery platform pack', 'weight_kg' => 23.0, 'price' => 899.00],
                        ],
                    ],
                    [
                        'brand' => 'nilfisk',
                        'series' => 'MC',
                        'spec' => [
                            'motor_type' => '1450 rpm motor',
                        ],
                        'items' => [
                            ['model' => 'MC 3C-170/820', 'pressure' => '170 bar', 'flow_rate' => '820 l/h', 'motor_type' => '1450 rpm motor', 'hose_length' => '10 m steel reinforced hose', 'accessories' => 'Foam sprayer and lance', 'weight_kg' => 31.0, 'price' => 749.00],
                            ['model' => 'MC 2C-150/650', 'pressure' => '150 bar', 'flow_rate' => '650 l/h', 'motor_type' => '1450 rpm aluminium motor', 'hose_length' => '10 m hose', 'accessories' => 'Quick coupling gun', 'weight_kg' => 28.0, 'price' => 629.00],
                            ['model' => 'MH 3C-180/780', 'pressure' => '180 bar', 'flow_rate' => '780 l/h', 'motor_type' => 'hot water burner unit', 'hose_length' => '15 m high-pressure hose', 'accessories' => 'Steam kit and lance', 'weight_kg' => 42.0, 'price' => 1199.00],
                        ],
                    ],
                    [
                        'brand' => 'bosch',
                        'series' => 'GHP',
                        'spec' => [
                            'motor_type' => 'induction motor',
                        ],
                        'items' => [
                            ['model' => 'GHP 5-75', 'pressure' => '185 bar', 'flow_rate' => '570 l/h', 'motor_type' => 'induction motor with brass pump', 'hose_length' => '10 m steel mesh hose', 'accessories' => 'Adjustable lance and hose reel', 'weight_kg' => 26.0, 'price' => 649.00],
                            ['model' => 'GHP 6-14', 'pressure' => '140 bar', 'flow_rate' => '650 l/h', 'motor_type' => 'induction motor', 'hose_length' => '10 m reinforced hose', 'accessories' => 'Flexible spray lance', 'weight_kg' => 27.0, 'price' => 599.00],
                            ['model' => 'GHP 8-15 XD', 'pressure' => '160 bar', 'flow_rate' => '560 l/h', 'motor_type' => 'induction motor', 'hose_length' => '15 m steel hose', 'accessories' => 'Detergent tank and reel', 'weight_kg' => 28.0, 'price' => 719.00],
                        ],
                    ],
                ],
            ],
        ];
    }

}
