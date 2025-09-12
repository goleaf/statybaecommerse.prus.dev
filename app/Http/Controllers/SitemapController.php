<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Resolve table names (supports sh_* prefixed fallback for tests)
        $tables = $this->resolveTables();
        $locales = collect(explode(',', (string) config('app.supported_locales', 'en')))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values();
        $urls = [];
        foreach ($locales as $loc) {
            $cached = Cache::get("sitemap:urls:{$loc}");
            if (is_array($cached)) {
                $urls = array_merge($urls, $cached);

                continue;
            }

            // Home & index pages per locale
            $urls[] = url('/' . $loc);
            $urls[] = url('/' . $loc . '/categories');
            $urls[] = url('/' . $loc . '/collections');
            $urls[] = url('/' . $loc . '/brands');

            // Categories with translated slugs fallback to base slug
            if (!Schema::hasTable($tables['categories'])) {
                continue;  // Skip dynamic sections when tables are missing in testing
            }
            $categoryQuery = DB::table($tables['categories'] . ' as c')
                ->leftJoin($tables['category_translations'] . ' as t', function ($join) use ($loc) {
                    $join->on('t.category_id', '=', 'c.id')->where('t.locale', '=', $loc);
                })
                ->limit(1000)
                ->selectRaw('COALESCE(t.slug, c.slug) as slug');
            if (Schema::hasColumn($tables['categories'], 'is_enabled')) {
                $categoryQuery->where('c.is_enabled', true);
            }
            $categorySlugs = $categoryQuery->pluck('slug');
            foreach ($categorySlugs as $slug) {
                $urls[] = url('/' . $loc . '/categories/' . $slug);
            }

            // Collections
            if (!Schema::hasTable($tables['collections'])) {
                continue;
            }
            $collectionQuery = DB::table($tables['collections'] . ' as c')
                ->leftJoin($tables['collection_translations'] . ' as t', function ($join) use ($loc) {
                    $join->on('t.collection_id', '=', 'c.id')->where('t.locale', '=', $loc);
                })
                ->limit(1000)
                ->selectRaw('COALESCE(t.slug, c.slug) as slug');
            if (Schema::hasColumn($tables['collections'], 'is_enabled')) {
                $collectionQuery->where('c.is_enabled', true);
            }
            $collectionSlugs = $collectionQuery->pluck('slug');
            foreach ($collectionSlugs as $slug) {
                $urls[] = url('/' . $loc . '/collections/' . $slug);
            }

            // Brands
            if (!Schema::hasTable($tables['brands'])) {
                continue;
            }
            $brandQuery = DB::table($tables['brands'] . ' as b')
                ->leftJoin($tables['brand_translations'] . ' as t', function ($join) use ($loc) {
                    $join->on('t.brand_id', '=', 'b.id')->where('t.locale', '=', $loc);
                })
                ->limit(1000)
                ->selectRaw('COALESCE(t.slug, b.slug) as slug');
            if (Schema::hasColumn($tables['brands'], 'is_enabled')) {
                $brandQuery->where('b.is_enabled', true);
            }
            $brandSlugs = $brandQuery->pluck('slug');
            foreach ($brandSlugs as $slug) {
                $urls[] = url('/' . $loc . '/brands/' . $slug);
            }

            // Products (visible & published)
            if (!Schema::hasTable($tables['products'])) {
                continue;
            }
            $productQuery = DB::table($tables['products'] . ' as p')
                ->leftJoin($tables['product_translations'] . ' as t', function ($join) use ($loc) {
                    $join->on('t.product_id', '=', 'p.id')->where('t.locale', '=', $loc);
                })
                ->limit(5000)
                ->selectRaw('COALESCE(t.slug, p.slug) as slug');
            if (Schema::hasColumn($tables['products'], 'is_visible')) {
                $productQuery->where('p.is_visible', true);
            }
            if (Schema::hasColumn($tables['products'], 'published_at')) {
                $productQuery->whereNotNull('p.published_at')->where('p.published_at', '<=', now());
            }
            $productSlugs = $productQuery->pluck('slug');
            foreach ($productSlugs as $slug) {
                $urls[] = url('/' . $loc . '/products/' . $slug);
            }

            // Legal pages (enabled) with translated slugs
            if (!Schema::hasTable($tables['legals'])) {
                continue;
            }
            $legalQuery = DB::table($tables['legals'] . ' as l')
                ->leftJoin($tables['legal_translations'] . ' as t', function ($join) use ($loc) {
                    $join->on('t.legal_id', '=', 'l.id')->where('t.locale', '=', $loc);
                })
                ->selectRaw('COALESCE(t.slug, l.slug) as slug');
            if (Schema::hasColumn($tables['legals'], 'is_enabled')) {
                $legalQuery->where('l.is_enabled', true);
            }
            $legalSlugs = $legalQuery->pluck('slug');
            foreach ($legalSlugs as $slug) {
                $urls[] = url('/' . $loc . '/legal/' . $slug);
            }

            // Cache per-locale URLs for 1 day
            $perLocale = array_values(array_filter($urls, fn($u) => str_starts_with($u, url('/' . $loc))));
            Cache::put("sitemap:urls:{$loc}", $perLocale, now()->addDay());
        }

        // Generate XML directly to avoid Blade compilation issues with XML declaration
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
            $xml .= '    </url>' . "\n";
        }
        
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Generate XML sitemap from URLs array
     */
    private function generateXmlSitemap(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
            $xml .= '    </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    public function locale(string $locale): Response
    {
        $tables = $this->resolveTables();
        $supported = collect(explode(',', (string) config('app.supported_locales', 'en')))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values();

        if (!$supported->contains($locale)) {
            $locale = (string) config('app.locale', 'en');
        }

        if ($cached = Cache::get("sitemap:urls:{$locale}")) {
            $xml = $this->generateXmlSitemap($cached);

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        $urls = [];

        // Home & index pages per locale
        $urls[] = url('/' . $locale);
        $urls[] = url('/' . $locale . '/categories');
        $urls[] = url('/' . $locale . '/collections');
        $urls[] = url('/' . $locale . '/brands');

        // Categories with translated slugs fallback to base slug
        if (!Schema::hasTable($tables['categories'])) {
            $xml = $this->generateXmlSitemap($urls);

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $categoryQuery = DB::table($tables['categories'] . ' as c')
            ->leftJoin($tables['category_translations'] . ' as t', function ($join) use ($locale) {
                $join->on('t.category_id', '=', 'c.id')->where('t.locale', '=', $locale);
            })
            ->limit(1000)
            ->selectRaw('COALESCE(t.slug, c.slug) as slug');
        if (Schema::hasColumn($tables['categories'], 'is_enabled')) {
            $categoryQuery->where('c.is_enabled', true);
        }
        $categorySlugs = $categoryQuery->pluck('slug');
        foreach ($categorySlugs as $slug) {
            $urls[] = url('/' . $locale . '/categories/' . $slug);
        }

        // Collections
        if (!Schema::hasTable($tables['collections'])) {
            $xml = $this->generateXmlSitemap($urls);

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $collectionQuery = DB::table($tables['collections'] . ' as c')
            ->leftJoin($tables['collection_translations'] . ' as t', function ($join) use ($locale) {
                $join->on('t.collection_id', '=', 'c.id')->where('t.locale', '=', $locale);
            })
            ->limit(1000)
            ->selectRaw('COALESCE(t.slug, c.slug) as slug');
        if (Schema::hasColumn($tables['collections'], 'is_enabled')) {
            $collectionQuery->where('c.is_enabled', true);
        }
        $collectionSlugs = $collectionQuery->pluck('slug');
        foreach ($collectionSlugs as $slug) {
            $urls[] = url('/' . $locale . '/collections/' . $slug);
        }

        // Brands
        if (!Schema::hasTable($tables['brands'])) {
            $xml = $this->generateXmlSitemap($urls);

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $brandQuery = DB::table($tables['brands'] . ' as b')
            ->leftJoin($tables['brand_translations'] . ' as t', function ($join) use ($locale) {
                $join->on('t.brand_id', '=', 'b.id')->where('t.locale', '=', $locale);
            })
            ->limit(1000)
            ->selectRaw('COALESCE(t.slug, b.slug) as slug');
        if (Schema::hasColumn($tables['brands'], 'is_enabled')) {
            $brandQuery->where('b.is_enabled', true);
        }
        $brandSlugs = $brandQuery->pluck('slug');
        foreach ($brandSlugs as $slug) {
            $urls[] = url('/' . $locale . '/brands/' . $slug);
        }

        // Products (visible & published)
        if (!Schema::hasTable($tables['products'])) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $productQuery = DB::table($tables['products'] . ' as p')
            ->leftJoin($tables['product_translations'] . ' as t', function ($join) use ($locale) {
                $join->on('t.product_id', '=', 'p.id')->where('t.locale', '=', $locale);
            })
            ->limit(5000)
            ->selectRaw('COALESCE(t.slug, p.slug) as slug');
        if (Schema::hasColumn($tables['products'], 'is_visible')) {
            $productQuery->where('p.is_visible', true);
        }
        if (Schema::hasColumn($tables['products'], 'published_at')) {
            $productQuery->whereNotNull('p.published_at')->where('p.published_at', '<=', now());
        }
        $productSlugs = $productQuery->pluck('slug');
        foreach ($productSlugs as $slug) {
            $urls[] = url('/' . $locale . '/products/' . $slug);
        }

        // Legal pages (enabled) with translated slugs
        if (!Schema::hasTable($tables['legals'])) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $legalQuery = DB::table($tables['legals'] . ' as l')
            ->leftJoin($tables['legal_translations'] . ' as t', function ($join) use ($locale) {
                $join->on('t.legal_id', '=', 'l.id')->where('t.locale', '=', $locale);
            })
            ->selectRaw('COALESCE(t.slug, l.slug) as slug');
        if (Schema::hasColumn($tables['legals'], 'is_enabled')) {
            $legalQuery->where('l.is_enabled', true);
        }
        $legalSlugs = $legalQuery->pluck('slug');
        foreach ($legalSlugs as $slug) {
            $urls[] = url('/' . $locale . '/legal/' . $slug);
        }

        Cache::put("sitemap:urls:{$locale}", $urls, now()->addDay());
        $xml = view('sitemap.xml', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function resolveTables(): array
    {
        // Map of base table names and their sh_* fallbacks used in tests
        $map = [
            'categories' => 'sh_categories',
            'category_translations' => 'sh_category_translations',
            'collections' => 'sh_collections',
            'collection_translations' => 'sh_collection_translations',
            'brands' => 'sh_brands',
            'brand_translations' => 'sh_brand_translations',
            'products' => 'sh_products',
            'product_translations' => 'sh_product_translations',
            'legals' => 'sh_legals',
            'legal_translations' => 'sh_legal_translations',
        ];

        $resolved = [];
        foreach ($map as $base => $fallback) {
            // Prefer sh_* tables when present (tests seed these), otherwise use base
            if (\Illuminate\Support\Facades\Schema::hasTable($fallback)) {
                $resolved[$base] = $fallback;
            } else {
                $resolved[$base] = $base;
            }
        }

        return $resolved;
    }
}
