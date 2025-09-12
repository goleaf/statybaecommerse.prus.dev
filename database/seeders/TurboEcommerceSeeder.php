<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * An ultra-fast, idempotent, and scalable seeder for large product catalogs.
 *
 * - Bulk upserts products by SKU
 * - Attaches brands, categories, and attributes in chunks
 * - Seeds translations for all supported locales
 * - Generates local placeholder images (GD) via LocalImageGeneratorService
 * - Avoids model events and N+1 where possible
 */
final class TurboEcommerceSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGen;
    /** @var array<int,string> */
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
        $brandIds = Brand::query()->enabled()->pluck('id')->values();
        $categoryIds = Category::query()->active()->pluck('id')->values();

        // Attributes and their values
        $attributes = Attribute::query()->enabled()->with(['values' => function ($q) {
            $q->enabled();
        }])->get();

        // Prepare a shared pool of images used across all products
        $this->buildSharedImagePool(100);

        // Generate products per brand in fast upserted chunks
        foreach ($brandIds->chunk(100) as $brandChunk) {
            foreach ($brandChunk as $brandId) {
                $this->seedProductsForBrand((int) $brandId, $categoryIds, $attributes, $locales);
            }
        }

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
     * Seed products for a given brand with relations, translations and images
     */
    private function seedProductsForBrand(int $brandId, $categoryIds, $attributes, array $locales): void
    {
        $current = Product::query()->where('brand_id', $brandId)->count();
        $missing = max(0, $this->productsPerBrand - $current);
        if ($missing === 0) {
            return;
        }

        $this->command?->info("— Brand {$brandId}: creating {$missing} products");

        $namePoolLt = $this->ltNamePool();

        // Create in chunks with upsert by SKU to be idempotent
        $createdSkuBatches = [];
        $now = now();

        for ($offset = 0; $offset < $missing; $offset += $this->chunkSize) {
            $batchSize = (int) min($this->chunkSize, $missing - $offset);
            $rows = [];
            $skus = [];

            for ($i = 0; $i < $batchSize; $i++) {
                $nameLt = $namePoolLt[array_rand($namePoolLt)];
                $sku = 'PRD-' . strtoupper(Str::random(10));
                $slug = Str::slug($nameLt . '-' . Str::random(6));
                $price = mt_rand(500, 250000) / 100;  // 5.00 - 2500.00

                $rows[] = [
                    'name' => $nameLt,  // base/default locale copy
                    'slug' => $slug,
                    'description' => '—',
                    'short_description' => '—',
                    'sku' => $sku,
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
                    'published_at' => $now,
                    'seo_title' => $nameLt,
                    'seo_description' => '—',
                    'brand_id' => $brandId,
                    'status' => 'published',
                    'type' => 'simple',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $skus[] = $sku;
            }

            // Upsert by SKU to avoid duplicates and stay fast
            DB::table('products')->upsert(
                $rows,
                ['sku'],
                [
                    'name', 'slug', 'description', 'short_description', 'price', 'sale_price', 'manage_stock', 'stock_quantity',
                    'low_stock_threshold', 'weight', 'length', 'width', 'height', 'is_visible', 'is_featured', 'published_at',
                    'seo_title', 'seo_description', 'brand_id', 'status', 'type', 'updated_at'
                ]
            );

            $createdSkuBatches[] = $skus;
        }

        // Resolve product IDs for the new/updated SKUs
        $allSkus = array_merge(...$createdSkuBatches);
        $productIdBySku = DB::table('products')->whereIn('sku', $allSkus)->pluck('id', 'sku');
        $productIds = collect($allSkus)->map(fn($s) => (int) ($productIdBySku[$s] ?? 0))->filter()->values();

        if ($productIds->isEmpty()) {
            return;
        }

        // Attach categories in bulk
        $this->attachCategories($productIds, $categoryIds);

        // Attach attributes in bulk
        $this->attachAttributes($productIds, $attributes);

        // Seed translations for all locales
        $this->seedProductTranslations($productIds, $locales);

        // Ensure images per product using shared image pool (chunked)
        $this->ensureImagesForProducts($productIds);
    }

    private function attachCategories($productIds, $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($productIds as $pid) {
            $attach = collect($categoryIds)->random(min($this->categoriesPerProduct, count($categoryIds)))->all();
            foreach ($attach as $cid) {
                $rows[] = [
                    'product_id' => (int) $pid,
                    'category_id' => (int) $cid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 2000) as $chunk) {
            DB::table('product_categories')->insertOrIgnore($chunk);
        }
    }

    private function attachAttributes($productIds, $attributes): void
    {
        if ($attributes->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($productIds as $pid) {
            $count = mt_rand($this->attributesPerProductMin, $this->attributesPerProductMax);
            $picked = $attributes->shuffle()->take($count);
            foreach ($picked as $attr) {
                /** @var Attribute $attr */
                $vals = $attr->values->where('is_enabled', true);
                $val = $vals->isNotEmpty() ? $vals->random() : null;
                if (!$val) {
                    continue;
                }
                $rows[] = [
                    'product_id' => (int) $pid,
                    'attribute_id' => (int) $attr->id,
                    'attribute_value_id' => (int) $val->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 2000) as $chunk) {
            DB::table('product_attributes')->insertOrIgnore($chunk);
        }
    }

    private function seedProductTranslations($productIds, array $locales): void
    {
        if (empty($locales)) {
            return;
        }

        $now = now();
        $base = DB::table('products')->whereIn('id', $productIds)->select(['id', 'name', 'slug'])->get();

        $rows = [];
        foreach ($base as $row) {
            foreach ($locales as $loc) {
                $rows[] = [
                    'product_id' => (int) $row->id,
                    'locale' => (string) $loc,
                    'name' => $this->translateLike($row->name, $loc),
                    'slug' => Str::slug($this->translateLike($row->name, $loc) . '-' . substr($row->slug, -6)),
                    'summary' => $this->translateLike('Profesionalus įrankis', $loc),
                    'description' => $this->translateLike('Aukštos kokybės produktas profesionalams ir mėgėjams.', $loc),
                    'seo_title' => $this->translateLike($row->name, $loc),
                    'seo_description' => $this->translateLike('Pirkite geriausia kaina. Greitas pristatymas.', $loc),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 2000) as $chunk) {
            DB::table('product_translations')->upsert(
                $chunk,
                ['product_id', 'locale'],
                ['name', 'slug', 'summary', 'description', 'seo_title', 'seo_description', 'updated_at']
            );
        }
    }

    private function ensureImagesForProducts($productIds): void
    {
        // Ensure pool is available
        if (empty($this->sharedImagePool)) {
            $this->buildSharedImagePool(100);
        }

        $ids = collect($productIds)->chunk(100);
        foreach ($ids as $chunk) {
            /** @var \Illuminate\Support\Collection<int, Product> $products */
            $products = Product::query()->whereIn('id', $chunk)->with(['images'])->get();

            foreach ($products as $product) {
                try {
                    $current = (int) $product->images()->count();
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
                            ProductImage::query()->create([
                                'product_id' => $product->id,
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
            'Elektrinis perforatorius', 'Kampinis šlifuoklis', 'Smūginis gręžtuvas', 'Suktuvas-gręžtuvas',
            'Vibracinė šlifavimo mašina', 'Diskinis pjūklas', 'Planuoklis', 'Frezeris', 'Grandininis pjūklas',
            'Profesionalus plaktukas', 'Statybinė gulsčiukė', 'Ruletė 10m', 'Universalus peilis', 'Raktų komplektas',
            'Atsuktuvų rinkinys', 'Replės elektrikui', 'Metalinė liniuotė', 'Kaltų rinkinys', 'Kampuotė',
            'Cemento mišinys', 'Gipso plokštės', 'Termoizoliacijos plokštės', 'Hidroizoliacijos plėvelė',
            'Statybinės putos', 'Akrilo hermetikas', 'Gruntavimo skystis', 'Fasadiniai dažai', 'Klijų mišinys',
            'Betono priedas', 'Apsauginiai akiniai', 'Darbo pirštinės', 'Apsauginis šalmas', 'Apsauginiai batai',
            'Respiratorius', 'Ausų apsaugos', 'Apsauginis diržas', 'Šviečianti liemenė', 'Pirmos pagalbos rinkinys'
        ];
    }
}
