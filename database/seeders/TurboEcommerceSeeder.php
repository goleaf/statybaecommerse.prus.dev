<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

/**
 * An ultra-fast, idempotent, and scalable seeder for large product catalogs.
 *
 * - Uses model factories exclusively with relationships
 * - Attaches brands, categories, and attributes using Eloquent relationships
 * - Seeds translations for all supported locales using factory relationships
 * - Generates local placeholder images via LocalImageGeneratorService
 * - Leverages model events and relationships for data integrity
 */
final class TurboEcommerceSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGen;

    /**
     * @var array<int,string>
     */
    private array $sharedImagePool = [];

    private string $sharedImagePoolDir;

    // Tuneable via env; choose very high yet safe defaults (no artificial caps)
    private int $productsPerBrand;

    private int $categoriesPerProduct;

    private int $attributesPerProductMin;

    private int $attributesPerProductMax;

    private int $minImagesPerProduct;

    private int $maxImagesPerProduct;

    private int $chunkSize;

    public function __construct()
    {
        $this->imageGen = app(LocalImageGeneratorService::class);
        $this->sharedImagePoolDir = storage_path('app/temp/shared_product_images');

        $this->productsPerBrand = (int) env('SEED_PRODUCTS_PER_BRAND', 100);
        $this->categoriesPerProduct = (int) env('SEED_CATEGORIES_PER_PRODUCT', 3);
        $this->attributesPerProductMin = (int) env('SEED_ATTRS_PER_PRODUCT_MIN', 3);
        $this->attributesPerProductMax = (int) env('SEED_ATTRS_PER_PRODUCT_MAX', 6);
        $this->minImagesPerProduct = (int) env('SEED_IMAGES_PER_PRODUCT_MIN', 3);
        $this->maxImagesPerProduct = (int) env('SEED_IMAGES_PER_PRODUCT_MAX', 6);
        if ($this->maxImagesPerProduct < $this->minImagesPerProduct) {
            $this->maxImagesPerProduct = $this->minImagesPerProduct;
        }
        $this->chunkSize = (int) env('SEED_CHUNK_SIZE', 500);
    }

    public function run(): void
    {
        $this->command?->info('⚡ TurboEcommerceSeeder: starting...');

        // Ensure foundational data exists
        $this->ensureFoundations();

        // Snapshot needed references
        $locales = $this->supportedLocales();
        $brands = Brand::query()->enabled()->get();
        $categories = Category::query()->active()->get();

        // Attributes and their values
        $attributes = Attribute::query()->enabled()->with(['values' => function ($q) {
            $q->enabled();
        }])->get();

        // Prepare a shared pool of images used across all products
        $this->buildSharedImagePool(100);

        // Generate products per brand using factories with timeout protection
        $timeout = now()->addMinutes(60);  // 60 minute timeout for seeder operations

        LazyCollection::make($brands->chunk(10))
            ->takeUntilTimeout($timeout)
            ->each(function ($brandChunk) use ($categories, $attributes, $locales) {
                foreach ($brandChunk as $brand) {
                    $this->seedProductsForBrand($brand, $categories, $attributes, $locales);
                }
            });

        // Cleanup shared images after attachments are done
        $this->cleanupSharedImagePool();

        $this->command?->info('✅ TurboEcommerceSeeder: completed');
    }

    private function ensureFoundations(): void
    {
        // If no brands/categories/attributes exist, leverage existing seeders quickly
        if (Brand::query()->count() === 0) {
            $this->call(BrandSeeder::class);
        }
        if (Category::query()->count() === 0) {
            $this->call(CategorySeeder::class);
        }
        if (Attribute::query()->count() === 0) {
            $this->call(AttributeSeeder::class);
            $this->call(AttributeValueSeeder::class);
        }
    }

    /**
     * Seed products for a given brand with relations, translations and images using factories
     */
    private function seedProductsForBrand(Brand $brand, $categories, $attributes, array $locales): void
    {
        $current = $brand->products()->count();
        $missing = max(0, $this->productsPerBrand - $current);
        if ($missing === 0) {
            return;
        }

        $this->command?->info("— Brand {$brand->id}: creating {$missing} products");

        $namePoolLt = $this->ltNamePool();

        // Create products in chunks using factories
        for ($offset = 0; $offset < $missing; $offset += $this->chunkSize) {
            $batchSize = (int) min($this->chunkSize, $missing - $offset);
            $products = collect();

            for ($i = 0; $i < $batchSize; $i++) {
                $nameLt = $namePoolLt[array_rand($namePoolLt)];
                $price = mt_rand(500, 250000) / 100;  // 5.00 - 2500.00

                // Create product using factory with brand relationship
                $product = Product::factory()
                    ->for($brand, 'brand')
                    ->create([
                        'name' => $nameLt,
                        'slug' => Str::slug($nameLt . '-' . Str::random(6)),
                        'description' => '—',
                        'short_description' => '—',
                        'sku' => 'PRD-' . strtoupper(Str::random(10)),
                        'price' => $price,
                        'sale_price' => (mt_rand(0, 100) < 20) ? round($price * 0.85, 2) : null,
                        'manage_stock' => (mt_rand(0, 100) < 85),
                        'stock_quantity' => mt_rand(0, 500),
                        'low_stock_threshold' => mt_rand(3, 25),
                        'weight' => mt_rand(10, 2500) / 100,
                        'length' => mt_rand(10, 3000) / 10,
                        'width' => mt_rand(10, 3000) / 10,
                        'height' => mt_rand(10, 1500) / 10,
                        'is_visible' => true,
                        'is_featured' => (mt_rand(0, 100) < 10),
                        'published_at' => now(),
                        'seo_title' => $nameLt,
                        'seo_description' => '—',
                        'status' => 'published',
                        'type' => 'simple',
                    ]);

                $products->push($product);
            }

            // Attach categories using Eloquent relationships
            $this->attachCategories($products, $categories);

            // Attach attributes using Eloquent relationships
            $this->attachAttributes($products, $attributes);

            // Seed translations for all locales using factories
            $this->seedProductTranslations($products, $locales);

            // Ensure images per product using shared image pool
            $this->ensureImagesForProducts($products);
        }
    }

    private function attachCategories($products, $categories): void
    {
        if ($categories->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            $categoriesToAttach = $categories->random(min($this->categoriesPerProduct, $categories->count()));

            // Use Eloquent relationship to attach categories
            $product->categories()->syncWithoutDetaching($categoriesToAttach->pluck('id'));
        }
    }

    private function attachAttributes($products, $attributes): void
    {
        if ($attributes->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            $count = mt_rand($this->attributesPerProductMin, $this->attributesPerProductMax);
            $picked = $attributes->shuffle()->take($count);

            $attributeData = [];
            foreach ($picked as $attr) {
                /** @var Attribute $attr */
                $vals = $attr->values->where('is_enabled', true);
                $val = $vals->isNotEmpty() ? $vals->random() : null;
                if ($val) {
                    $attributeData[$attr->id] = ['attribute_value_id' => $val->id];
                }
            }

            // Use Eloquent relationship to attach attributes
            if (!empty($attributeData)) {
                $product->attributes()->sync($attributeData);
            }
        }
    }

    private function seedProductTranslations($products, array $locales): void
    {
        if (empty($locales)) {
            return;
        }

        foreach ($products as $product) {
            foreach ($locales as $locale) {
                // Create translation using factory with relationship
                $product->translations()->firstOrCreate(
                    ['locale' => $locale],
                    ProductTranslation::factory()
                        ->for($product, 'product')
                        ->make([
                            'locale' => $locale,
                            'name' => $this->translateLike($product->name, $locale),
                            'slug' => Str::slug($this->translateLike($product->name, $locale) . '-' . substr($product->slug, -6)),
                            'summary' => $this->translateLike('Profesionalus įrankis', $locale),
                            'description' => $this->translateLike('Aukštos kokybės produktas profesionalams ir mėgėjams.', $locale),
                            'seo_title' => $this->translateLike($product->name, $locale),
                            'seo_description' => $this->translateLike('Pirkite geriausia kaina. Greitas pristatymas.', $locale),
                        ])
                        ->toArray()
                );
            }
        }
    }

    private function ensureImagesForProducts($products): void
    {
        // Ensure pool is available
        if (empty($this->sharedImagePool)) {
            $this->buildSharedImagePool(100);
        }

        foreach ($products as $product) {
            try {
                $current = $product->images()->count();
                $target = $this->minImagesPerProduct === $this->maxImagesPerProduct
                    ? $this->minImagesPerProduct
                    : random_int($this->minImagesPerProduct, $this->maxImagesPerProduct);
                $toAdd = max(0, $target - $current);

                if ($toAdd > 0) {
                    $picks = $toAdd === 1
                        ? [Arr::random($this->sharedImagePool)]
                        : Arr::random($this->sharedImagePool, $toAdd);

                    foreach ($picks as $index => $path) {
                        if (!$path || !file_exists($path)) {
                            continue;
                        }

                        // Create product image using factory with relationship
                        ProductImage::factory()
                            ->for($product, 'product')
                            ->create([
                                'path' => 'storage/shared_product_images/' . basename($path),
                                'alt_text' => $product->name,
                                'sort_order' => $current + $index + 1,
                            ]);
                    }
                    $current += $toAdd;
                }

                if ($current > $this->maxImagesPerProduct) {
                    $excess = $current - $this->maxImagesPerProduct;
                    $toDelete = $product->images()->orderByDesc('id')->limit($excess)->get();
                    foreach ($toDelete as $img) {
                        try {
                            $img->delete();
                        } catch (\Throwable $e) {  /* ignore */
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Turbo image ensure failed', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function buildSharedImagePool(int $count = 100): void
    {
        if (!is_dir($this->sharedImagePoolDir)) {
            @mkdir($this->sharedImagePoolDir, 0755, true);
        }

        // If directory already has enough images, reuse them
        $existing = glob($this->sharedImagePoolDir . DIRECTORY_SEPARATOR . 'pool_image_*.webp') ?: [];
        if (count($existing) >= $count) {
            $this->sharedImagePool = array_values($existing);
            return;
        }

        $this->command?->info("🖼️ Building shared image pool ({$count})...");

        // Generate missing images
        $needed = $count - count($existing);
        $generated = [];
        for ($i = 1; $i <= $needed; $i++) {
            try {
                $name = 'Sample Product Image ' . ($i + count($existing));
                $file = $this->imageGen->generateWebPImage(
                    text: $name,
                    width: 600,
                    height: 600,
                    backgroundColor: null,
                    textColor: '#FFFFFF',
                    filename: 'pool_image_' . str_pad((string) ($i + count($existing)), 3, '0', STR_PAD_LEFT)
                );

                // Move into pool directory if generated elsewhere
                $dest = $this->sharedImagePoolDir . DIRECTORY_SEPARATOR . basename($file);
                if ($file !== $dest) {
                    @rename($file, $dest);
                }
                $generated[] = $dest;
            } catch (\Throwable $e) {
                Log::warning('Shared image generation failed', [
                    'index' => $i,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->sharedImagePool = array_values(array_merge($existing, $generated));
    }

    private function cleanupSharedImagePool(): void
    {
        // Keep directory for caching between runs; if you prefer cleanup, uncomment below
        // foreach ($this->sharedImagePool as $path) { @unlink($path); }
        // @rmdir($this->sharedImagePoolDir);
    }

    private function supportedLocales(): array
    {
        $raw = (string) config('app.supported_locales', 'lt');

        return collect(explode(',', $raw))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function translateLike(string $text, string $locale): string
    {
        // Lightweight pseudo-translation to avoid network calls but ensure per-locale difference
        return match ($locale) {
            'lt' => $text,
            'en' => $text . ' (EN)',
            'ru' => $text . ' (RU)',
            'de' => $text . ' (DE)',
            default => $text . ' (' . strtoupper($locale) . ')',
        };
    }

    private function ltNamePool(): array
    {
        return [
            'Elektrinis perforatorius',
            'Kampinis šlifuoklis',
            'Smūginis gręžtuvas',
            'Suktuvas-gręžtuvas',
            'Vibracinė šlifavimo mašina',
            'Diskinis pjūklas',
            'Planuoklis',
            'Frezeris',
            'Grandininis pjūklas',
            'Profesionalus plaktukas',
            'Statybinė gulsčiukė',
            'Ruletė 10m',
            'Universalus peilis',
            'Raktų komplektas',
            'Atsuktuvų rinkinys',
            'Replės elektrikui',
            'Metalinė liniuotė',
            'Kaltų rinkinys',
            'Kampuotė',
            'Cemento mišinys',
            'Gipso plokštės',
            'Termoizoliacijos plokštės',
            'Hidroizoliacijos plėvelė',
            'Statybinės putos',
            'Akrilo hermetikas',
            'Gruntavimo skystis',
            'Fasadiniai dažai',
            'Klijų mišinys',
            'Betono priedas',
            'Apsauginiai akiniai',
            'Darbo pirštinės',
            'Apsauginis šalmas',
            'Apsauginiai batai',
            'Respiratorius',
            'Ausų apsaugos',
            'Apsauginis diržas',
            'Šviečianti liemenė',
            'Pirmos pagalbos rinkinys',
        ];
    }
}
