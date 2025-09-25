<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class XmlCatalogService
{
    public function import(string $xmlPath, array $options = []): array
    {
        if (! is_file($xmlPath)) {
            return ['categories' => ['created' => 0, 'updated' => 0], 'products' => ['created' => 0, 'updated' => 0]];
        }

        $xml = simplexml_load_file($xmlPath, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            return ['categories' => ['created' => 0, 'updated' => 0], 'products' => ['created' => 0, 'updated' => 0]];
        }

        $only = $options['only'] ?? 'all';
        $result = [
            'categories' => ['created' => 0, 'updated' => 0],
            'products' => ['created' => 0, 'updated' => 0],
        ];

        DB::transaction(function () use ($xml, $only, &$result, $options): void {
            if ($only === 'all' || $only === 'categories') {
                $result['categories'] = $this->importCategories($xml->categories ?? null);
            }
            if ($only === 'all' || $only === 'products') {
                $result['products'] = $this->importProducts($xml->products ?? null, $options);
            }
        });

        return $result;
    }

    public function export(string $xmlPath, array $options = []): string
    {
        $only = $options['only'] ?? 'all';

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $catalog = $doc->createElement('catalog');
        $doc->appendChild($catalog);

        if ($only === 'all' || $only === 'categories') {
            $categoriesEl = $doc->createElement('categories');
            $catalog->appendChild($categoriesEl);

            Category::query()->withoutGlobalScopes()->with(['translations', 'parent'])->orderBy('id')->chunk(500, function ($chunk) use ($doc, $categoriesEl): void {
                foreach ($chunk as $category) {
                    $catEl = $doc->createElement('category');

                    $catEl->appendChild($doc->createElement('slug', (string) $category->slug));
                    if (! is_null($category->parent_id)) {
                        $catEl->appendChild($doc->createElement('parent_slug', (string) optional($category->parent)->slug));
                    }

                    // Base fields
                    $baseEl = $doc->createElement('base');
                    $this->appendIfNotNull($doc, $baseEl, 'is_enabled', $this->boolToString($category->is_enabled ?? false));
                    $this->appendIfNotNull($doc, $baseEl, 'is_visible', $this->boolToString($category->is_visible ?? false));
                    $this->appendIfNotNull($doc, $baseEl, 'sort_order', (string) ($category->sort_order ?? 0));
                    $this->appendIfNotNull($doc, $baseEl, 'show_in_menu', $this->boolToString($category->show_in_menu ?? false));
                    $this->appendIfNotNull($doc, $baseEl, 'product_limit', (string) ($category->product_limit ?? ''));
                    if ($baseEl->childNodes->length > 0) {
                        $catEl->appendChild($baseEl);
                    }

                    // Translations
                    $translationsEl = $doc->createElement('translations');
                    foreach ($category->translations as $tr) {
                        $tEl = $doc->createElement('translation');
                        $tEl->setAttribute('locale', (string) $tr->locale);
                        $this->appendIfNotNull($doc, $tEl, 'name', (string) $tr->name);
                        $this->appendIfNotNull($doc, $tEl, 'description', (string) ($tr->description ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'short_description', (string) ($tr->short_description ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'seo_title', (string) ($tr->seo_title ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'seo_description', (string) ($tr->seo_description ?? ''));
                        $translationsEl->appendChild($tEl);
                    }
                    $catEl->appendChild($translationsEl);

                    $categoriesEl->appendChild($catEl);
                }
            });
        }

        if ($only === 'all' || $only === 'products') {
            $productsEl = $doc->createElement('products');
            $catalog->appendChild($productsEl);

            Product::query()->withoutGlobalScopes()->with(['translations', 'brand', 'categories', 'images'])->orderBy('id')->chunk(250, function ($chunk) use ($doc, $productsEl): void {
                foreach ($chunk as $product) {
                    $pEl = $doc->createElement('product');
                    $this->appendIfNotNull($doc, $pEl, 'sku', (string) ($product->sku ?? ''));
                    $this->appendIfNotNull($doc, $pEl, 'slug', (string) ($product->slug ?? ''));

                    // Base
                    $baseEl = $doc->createElement('base');
                    $fields = [
                        'price' => $product->price,
                        'compare_price' => $product->compare_price,
                        'cost_price' => $product->cost_price,
                        'sale_price' => $product->sale_price,
                        'weight' => $product->weight,
                        'length' => $product->length,
                        'width' => $product->width,
                        'height' => $product->height,
                        'status' => $product->status,
                        'type' => $product->type,
                        'brand_id' => $product->brand_id,
                        'tax_class' => $product->tax_class,
                        'shipping_class' => $product->shipping_class,
                        'manage_stock' => $this->boolToString((bool) $product->manage_stock),
                        'track_stock' => $this->boolToString((bool) $product->track_stock),
                        'allow_backorder' => $this->boolToString((bool) $product->allow_backorder),
                        'stock_quantity' => $product->stock_quantity,
                        'low_stock_threshold' => $product->low_stock_threshold,
                        'minimum_quantity' => $product->minimum_quantity,
                        'is_visible' => $this->boolToString((bool) $product->is_visible),
                        'is_featured' => $this->boolToString((bool) $product->is_featured),
                        'is_requestable' => $this->boolToString((bool) $product->is_requestable),
                    ];
                    foreach ($fields as $k => $v) {
                        if ($v !== null && $v !== '') {
                            $this->appendIfNotNull($doc, $baseEl, $k, (string) $v);
                        }
                    }
                    if ($baseEl->childNodes->length > 0) {
                        $pEl->appendChild($baseEl);
                    }

                    // Categories
                    $pcEl = $doc->createElement('categories');
                    foreach ($product->categories as $cat) {
                        $cEl = $doc->createElement('category_slug', (string) $cat->slug);
                        $pcEl->appendChild($cEl);
                    }
                    $pEl->appendChild($pcEl);

                    // Translations
                    $translationsEl = $doc->createElement('translations');
                    foreach ($product->translations as $tr) {
                        $tEl = $doc->createElement('translation');
                        $tEl->setAttribute('locale', (string) $tr->locale);
                        $this->appendIfNotNull($doc, $tEl, 'name', (string) $tr->name);
                        $this->appendIfNotNull($doc, $tEl, 'slug', (string) ($tr->slug ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'description', (string) ($tr->description ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'short_description', (string) ($tr->short_description ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'seo_title', (string) ($tr->seo_title ?? ''));
                        $this->appendIfNotNull($doc, $tEl, 'seo_description', (string) ($tr->seo_description ?? ''));
                        $translationsEl->appendChild($tEl);
                    }
                    $pEl->appendChild($translationsEl);

                    // Images
                    $imagesEl = $doc->createElement('images');
                    foreach ($product->images()->orderBy('sort_order')->get() as $img) {
                        $path = (string) $img->path;
                        if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://') && ! str_starts_with($path, '/')) {
                            $path = asset(trim($path, '/'));
                        }
                        $iEl = $doc->createElement('image');
                        $iEl->setAttribute('src', $path);
                        if (! empty($img->alt_text)) {
                            $iEl->setAttribute('alt', (string) $img->alt_text);
                        }
                        $imagesEl->appendChild($iEl);
                    }
                    $pEl->appendChild($imagesEl);

                    $productsEl->appendChild($pEl);
                }
            });
        }

        $xml = $doc->saveXML();
        if ($xmlPath !== '') {
            file_put_contents($xmlPath, $xml);
        }

        return $xml ?: '';
    }

    private function importCategories($categoriesNode): array
    {
        $created = 0;
        $updated = 0;
        if ($categoriesNode === null) {
            return ['created' => 0, 'updated' => 0];
        }

        $items = [];
        foreach ($categoriesNode->category as $c) {
            $items[] = $c;
        }

        // First pass: create/update base categories without parent relations
        $pendingParents = [];
        $processed = 0;
        foreach ($items as $c) {
            $slug = trim((string) ($c->slug ?? ''));
            if ($slug === '') {
                $slug = Str::slug((string) ($c->translations->translation[0]->name ?? Str::uuid()->toString()));
            }

            $payload = $this->filterExistingColumns('categories', [
                'slug' => $slug,
                'is_enabled' => $this->toBool((string) ($c->base->is_enabled ?? '1')),
                'is_visible' => $this->toBool((string) ($c->base->is_visible ?? '1')),
                'sort_order' => (int) ((string) ($c->base->sort_order ?? '0')),
                'show_in_menu' => $this->toBool((string) ($c->base->show_in_menu ?? '0')),
                'product_limit' => (int) ((string) ($c->base->product_limit ?? '0')),
            ]);

            $category = Category::query()->withTrashed()->where('slug', $slug)->first();
            if (! $category) {
                $category = Category::query()->withoutGlobalScopes()->withTrashed()->where('slug', $slug)->first();
            }
            $nameLt = '';
            if (isset($c->translations)) {
                foreach ($c->translations->translation as $tt) {
                    if ((string) ($tt['locale'] ?? '') === 'lt') {
                        $nameLt = trim((string) ($tt->name ?? ''));
                        break;
                    }
                }
            }
            if ($nameLt !== '') {
                $payload['name'] = $nameLt;
            }

            if ($category) {
                if (method_exists($category, 'trashed') && $category->trashed()) {
                    $category->restore();
                    $category->update($payload);
                    $created++;
                } else {
                    $category->update($payload);
                    $updated++;
                }
            } else {
                $category = Category::query()->create(array_merge(['name' => $payload['name'] ?? $slug], $payload));
                $created++;
            }

            $pendingParents[$slug] = trim((string) ($c->parent_slug ?? ''));
            $processed++;

            // Translations
            if (isset($c->translations)) {
                foreach ($c->translations->translation as $t) {
                    $locale = (string) $t['locale'] ?: 'lt';
                    $trPayload = [
                        'name' => trim((string) ($t->name ?? '')),
                        'description' => trim((string) ($t->description ?? '')),
                        'short_description' => trim((string) ($t->short_description ?? '')),
                        'seo_title' => trim((string) ($t->seo_title ?? '')),
                        'seo_description' => trim((string) ($t->seo_description ?? '')),
                    ];
                    $trPayload = array_filter($trPayload, fn ($v) => $v !== '' && $v !== null);
                    if (! empty($trPayload)) {
                        $category->updateTranslation($locale, $trPayload);
                    }
                }
            }
        }

        // Second pass: assign parents by slug
        foreach ($pendingParents as $slug => $parentSlug) {
            if ($parentSlug === '') {
                continue;
            }
            $category = Category::query()->withoutGlobalScopes()->withTrashed()->where('slug', $slug)->first();
            $parent = Category::query()->withoutGlobalScopes()->withTrashed()->where('slug', $parentSlug)->first();
            if ($category && $parent && Schema::hasColumn('categories', 'parent_id')) {
                $category->parent()->associate($parent);
                $category->save();
            }
        }

        if (($created + $updated) === 0 && $processed > 0) {
            $updated = $processed;
        }

        return ['created' => $created, 'updated' => $updated];
    }

    private function importProducts($productsNode, array $options = []): array
    {
        $created = 0;
        $updated = 0;
        if ($productsNode === null) {
            return ['created' => 0, 'updated' => 0];
        }

        $processed = 0;
        foreach ($productsNode->product as $p) {
            $sku = trim((string) ($p->sku ?? ''));
            $slug = trim((string) ($p->slug ?? ''));

            $product = null;
            if ($sku !== '') {
                $product = Product::query()->withoutGlobalScopes()->withTrashed()->where('sku', $sku)->first();
            }
            if (! $product && $slug !== '') {
                $product = Product::query()->withoutGlobalScopes()->withTrashed()->where('slug', $slug)->first();
            }

            // Build payload from base
            $base = $p->base ?? null;
            $payload = [];
            if ($sku !== '') {
                $payload['sku'] = $sku;
            }
            if ($slug !== '') {
                $payload['slug'] = $slug;
            }

            if (! isset($payload['slug']) || $payload['slug'] === '') {
                // Prefer LT translation name for slug, fallback to SKU, then random
                $ltName = '';
                if (isset($p->translations)) {
                    foreach ($p->translations->translation as $tt) {
                        if ((string) ($tt['locale'] ?? '') === 'lt') {
                            $ltName = trim((string) ($tt->name ?? ''));
                            break;
                        }
                    }
                }
                $baseForSlug = $ltName !== '' ? $ltName : ($sku !== '' ? $sku : Str::uuid()->toString());
                $payload['slug'] = Str::slug($baseForSlug);
            }

            $map = [
                'price',
                'compare_price',
                'cost_price',
                'sale_price',
                'weight',
                'length',
                'width',
                'height',
                'status',
                'type',
                'brand_id',
                'tax_class',
                'shipping_class',
                'stock_quantity',
                'low_stock_threshold',
                'minimum_quantity',
            ];
            foreach ($map as $field) {
                if (isset($base->{$field}) && ((string) $base->{$field}) !== '') {
                    $payload[$field] = is_numeric((string) $base->{$field}) ? (float) ((string) $base->{$field}) : (string) $base->{$field};
                }
            }
            $bools = ['manage_stock', 'track_stock', 'allow_backorder', 'is_visible', 'is_featured', 'is_requestable'];
            foreach ($bools as $bf) {
                if (isset($base->{$bf})) {
                    $payload[$bf] = $this->toBool((string) $base->{$bf});
                }
            }

            $payload = $this->filterExistingColumns('products', $payload);

            if ($product) {
                if (method_exists($product, 'trashed') && $product->trashed()) {
                    $product->restore();
                    $product->update($payload);
                    $created++;
                } else {
                    $product->update($payload);
                    $updated++;
                }
            } else {
                $nameLt = '';
                if (isset($p->translations)) {
                    foreach ($p->translations->translation as $tt) {
                        if ((string) ($tt['locale'] ?? '') === 'lt') {
                            $nameLt = trim((string) ($tt->name ?? ''));
                            break;
                        }
                    }
                }
                $baseName = $nameLt !== '' ? $nameLt : ($payload['slug'] ?? ($sku !== '' ? $sku : 'product'));
                $payload['name'] = $baseName;
                if (! isset($payload['slug']) || $payload['slug'] === '') {
                    $payload['slug'] = Str::slug($payload['name'] ?? $sku ?? Str::uuid()->toString());
                }
                try {
                    $product = Product::query()->create($payload);
                    $created++;
                } catch (\Throwable $e) {
                    // Handle unique constraint (e.g., soft-deleted product exists with same SKU/slug)
                    $existing = null;
                    if ($sku !== '') {
                        $existing = Product::query()->withoutGlobalScopes()->withTrashed()->where('sku', $sku)->first();
                    }
                    if (! $existing && ($payload['slug'] ?? '') !== '') {
                        $existing = Product::query()->withoutGlobalScopes()->withTrashed()->where('slug', $payload['slug'])->first();
                    }
                    if ($existing) {
                        if (method_exists($existing, 'trashed') && $existing->trashed()) {
                            $existing->restore();
                        }
                        $existing->update($payload);
                        $product = $existing;
                        $updated++;
                    } else {
                        throw $e;
                    }
                }
            }

            // Categories by slug
            if (isset($p->categories)) {
                $slugs = [];
                foreach ($p->categories->category_slug as $cSlug) {
                    $s = trim((string) $cSlug);
                    if ($s !== '') {
                        $slugs[] = $s;
                    }
                }
                if (! empty($slugs)) {
                    $categoryIds = Category::query()->withoutGlobalScopes()->withTrashed()->whereIn('slug', array_values(array_unique($slugs)))->pluck('id')->all();
                    if (! empty($categoryIds)) {
                        $product->categories()->syncWithoutDetaching($categoryIds);
                    }
                }
            }

            // Translations
            if (isset($p->translations)) {
                foreach ($p->translations->translation as $t) {
                    $locale = (string) $t['locale'] ?: 'lt';
                    $trPayload = [
                        'name' => trim((string) ($t->name ?? '')),
                        'slug' => trim((string) ($t->slug ?? '')),
                        'description' => trim((string) ($t->description ?? '')),
                        'short_description' => trim((string) ($t->short_description ?? '')),
                        'seo_title' => trim((string) ($t->seo_title ?? '')),
                        'seo_description' => trim((string) ($t->seo_description ?? '')),
                    ];
                    $trPayload = array_filter($trPayload, fn ($v) => $v !== '' && $v !== null);
                    if (! empty($trPayload)) {
                        $product->updateTranslation($locale, $trPayload);
                    }
                }
            }

            // Images
            if (isset($p->images)) {
                $index = 1;
                foreach ($p->images->image as $img) {
                    $src = trim((string) ($img['src'] ?? ''));
                    $alt = trim((string) ($img['alt'] ?? ''));
                    if ($src === '') {
                        continue;
                    }

                    $storedPath = $src;
                    if ($this->shouldDownloadImages($options)) {
                        $storedPath = $this->downloadAndStoreImage($src, $product, $index);
                    }

                    if ($storedPath !== '') {
                        ProductImage::query()->create([
                            'product_id' => $product->id,
                            'path' => $storedPath,
                            'alt_text' => $alt !== '' ? $alt : ($payload['name'] ?? $product->name ?? ''),
                            'sort_order' => $index,
                        ]);
                        $index++;
                    }
                }
            }
            $processed++;
        }

        if (($created + $updated) === 0 && $processed > 0) {
            $updated = $processed;
        }

        return ['created' => $created, 'updated' => $updated];
    }

    private function appendIfNotNull(\DOMDocument $doc, \DOMElement $parent, string $name, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $parent->appendChild($doc->createElement($name, $value));
    }

    private function boolToString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    private function toBool(string $value): bool
    {
        $v = strtolower(trim($value));

        return in_array($v, ['1', 'true', 'yes', 'y'], true);
    }

    private function filterExistingColumns(string $table, array $payload): array
    {
        $filtered = [];
        foreach ($payload as $key => $value) {
            if (Schema::hasColumn($table, $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function shouldDownloadImages(array $options = []): bool
    {
        return (bool) ($options['download_images'] ?? true);
    }

    private function downloadAndStoreImage(string $src, Product $product, int $index): string
    {
        try {
            $contents = null;
            $extension = 'jpg';
            if (str_starts_with($src, 'data:')) {
                // data URI
                if (preg_match('/^data:(.*?);base64,(.*)$/', $src, $m)) {
                    $mime = strtolower($m[1] ?? 'image/jpeg');
                    $extension = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                        default => 'jpg',
                    };
                    $contents = base64_decode($m[2] ?? '', true) ?: null;
                }
            } else {
                $resp = Http::timeout(15)->retry(1, 100)->get($src);
                if ($resp->successful()) {
                    $contents = $resp->body();
                    $contentType = $resp->header('content-type');
                    if ($contentType) {
                        $contentType = strtolower((string) $contentType);
                        $extension = str_contains($contentType, 'png') ? 'png' : (str_contains($contentType, 'webp') ? 'webp' : (str_contains($contentType, 'gif') ? 'gif' : 'jpg'));
                    }
                }
            }
            if (! $contents) {
                return '';
            }
            $dir = 'product-images/'.(string) $product->id;
            $filename = 'image-'.$index.'-'.Str::random(8).'.'.$extension;
            $path = $dir.'/'.$filename;
            Storage::disk('public')->put($path, $contents);

            return 'storage/'.$path;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
