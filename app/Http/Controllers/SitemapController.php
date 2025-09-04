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
            if (!Schema::hasTable('categories')) {
                continue;  // Skip dynamic sections when tables are missing in testing
            }
            $categoryQuery = DB::table('categories as c')
                ->leftJoin('category_translations as t', function ($join) use ($loc) {
                    $join->on('t.category_id', '=', 'c.id')->where('t.locale', '=', $loc);
                })
                ->limit(1000)
                ->selectRaw('COALESCE(t.slug, c.slug) as slug');
            if (Schema::hasColumn('categories', 'is_enabled')) {
                $categoryQuery->where('c.is_enabled', true);
            }
            $categorySlugs = $categoryQuery->pluck('slug');
            foreach ($categorySlugs as $slug) {
                $urls[] = url('/' . $loc . '/categories/' . $slug);
            }

            // Collections
            if (!Schema::hasTable('collections')) {
                continue;
            }
            $collectionQuery = DB::table('collections as c')
                ->leftJoin('collection_translations as t', function ($join) use ($loc) {
                    $join->on('t.collection_id', '=', 'c.id')->where('t.locale', '=', $loc);
                })
                ->limit(1000)
                ->selectRaw('COALESCE(t.slug, c.slug) as slug');
            if (Schema::hasColumn('collections', 'is_enabled')) {
                $collectionQuery->where('c.is_enabled', true);
            }
            $collectionSlugs = $collectionQuery->pluck('slug');
            foreach ($collectionSlugs as $slug) {
                $urls[] = url('/' . $loc . '/collections/' . $slug);
            }

            // Brands
            if (!Schema::hasTable('brands')) {
                continue;
            }
            $brandQuery = DB::table('brands as b')
                ->leftJoin('brand_translations as t', function ($join) use ($loc) {
                    $join->on('t.brand_id', '=', 'b.id')->where('t.locale', '=', $loc);
                })
                ->limit(1000)
                ->selectRaw('COALESCE(t.slug, b.slug) as slug');
            if (Schema::hasColumn('brands', 'is_enabled')) {
                $brandQuery->where('b.is_enabled', true);
            }
            $brandSlugs = $brandQuery->pluck('slug');
            foreach ($brandSlugs as $slug) {
                $urls[] = url('/' . $loc . '/brands/' . $slug);
            }

            // Products (visible & published)
            if (!Schema::hasTable('products')) {
                continue;
            }
            $productQuery = DB::table('products as p')
                ->leftJoin('product_translations as t', function ($join) use ($loc) {
                    $join->on('t.product_id', '=', 'p.id')->where('t.locale', '=', $loc);
                })
                ->limit(5000)
                ->selectRaw('COALESCE(t.slug, p.slug) as slug');
            if (Schema::hasColumn('products', 'is_visible')) {
                $productQuery->where('p.is_visible', true);
            }
            if (Schema::hasColumn('products', 'published_at')) {
                $productQuery->whereNotNull('p.published_at')->where('p.published_at', '<=', now());
            }
            $productSlugs = $productQuery->pluck('slug');
            foreach ($productSlugs as $slug) {
                $urls[] = url('/' . $loc . '/products/' . $slug);
            }

            // Legal pages (enabled) with translated slugs
            if (!Schema::hasTable('legals')) {
                continue;
            }
            $legalQuery = DB::table('legals as l')
                ->leftJoin('legal_translations as t', function ($join) use ($loc) {
                    $join->on('t.legal_id', '=', 'l.id')->where('t.locale', '=', $loc);
                })
                ->selectRaw('COALESCE(t.slug, l.slug) as slug');
            if (Schema::hasColumn('legals', 'is_enabled')) {
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

        $xml = view('sitemap.xml', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function locale(string $locale): Response
    {
        $supported = collect(explode(',', (string) config('app.supported_locales', 'en')))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values();

        if (!$supported->contains($locale)) {
            $locale = (string) config('app.locale', 'en');
        }

        if ($cached = Cache::get("sitemap:urls:{$locale}")) {
            $xml = view('sitemap.xml', ['urls' => $cached])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        $urls = [];

        // Home & index pages per locale
        $urls[] = url('/' . $locale);
        $urls[] = url('/' . $locale . '/categories');
        $urls[] = url('/' . $locale . '/collections');
        $urls[] = url('/' . $locale . '/brands');

        // Categories with translated slugs fallback to base slug
        if (!Schema::hasTable('categories')) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $categoryQuery = DB::table('categories as c')
            ->leftJoin('category_translations as t', function ($join) use ($locale) {
                $join->on('t.category_id', '=', 'c.id')->where('t.locale', '=', $locale);
            })
            ->limit(1000)
            ->selectRaw('COALESCE(t.slug, c.slug) as slug');
        if (Schema::hasColumn('categories', 'is_enabled')) {
            $categoryQuery->where('c.is_enabled', true);
        }
        $categorySlugs = $categoryQuery->pluck('slug');
        foreach ($categorySlugs as $slug) {
            $urls[] = url('/' . $locale . '/categories/' . $slug);
        }

        // Collections
        if (!Schema::hasTable('collections')) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $collectionQuery = DB::table('collections as c')
            ->leftJoin('collection_translations as t', function ($join) use ($locale) {
                $join->on('t.collection_id', '=', 'c.id')->where('t.locale', '=', $locale);
            })
            ->limit(1000)
            ->selectRaw('COALESCE(t.slug, c.slug) as slug');
        if (Schema::hasColumn('collections', 'is_enabled')) {
            $collectionQuery->where('c.is_enabled', true);
        }
        $collectionSlugs = $collectionQuery->pluck('slug');
        foreach ($collectionSlugs as $slug) {
            $urls[] = url('/' . $locale . '/collections/' . $slug);
        }

        // Brands
        if (!Schema::hasTable('brands')) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $brandQuery = DB::table('brands as b')
            ->leftJoin('brand_translations as t', function ($join) use ($locale) {
                $join->on('t.brand_id', '=', 'b.id')->where('t.locale', '=', $locale);
            })
            ->limit(1000)
            ->selectRaw('COALESCE(t.slug, b.slug) as slug');
        if (Schema::hasColumn('brands', 'is_enabled')) {
            $brandQuery->where('b.is_enabled', true);
        }
        $brandSlugs = $brandQuery->pluck('slug');
        foreach ($brandSlugs as $slug) {
            $urls[] = url('/' . $locale . '/brands/' . $slug);
        }

        // Products (visible & published)
        if (!Schema::hasTable('products')) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $productQuery = DB::table('products as p')
            ->leftJoin('product_translations as t', function ($join) use ($locale) {
                $join->on('t.product_id', '=', 'p.id')->where('t.locale', '=', $locale);
            })
            ->limit(5000)
            ->selectRaw('COALESCE(t.slug, p.slug) as slug');
        if (Schema::hasColumn('products', 'is_visible')) {
            $productQuery->where('p.is_visible', true);
        }
        if (Schema::hasColumn('products', 'published_at')) {
            $productQuery->whereNotNull('p.published_at')->where('p.published_at', '<=', now());
        }
        $productSlugs = $productQuery->pluck('slug');
        foreach ($productSlugs as $slug) {
            $urls[] = url('/' . $locale . '/products/' . $slug);
        }

        // Legal pages (enabled) with translated slugs
        if (!Schema::hasTable('legals')) {
            $xml = view('sitemap.xml', ['urls' => $urls])->render();

            return response($xml, 200)->header('Content-Type', 'application/xml');
        }
        $legalQuery = DB::table('legals as l')
            ->leftJoin('legal_translations as t', function ($join) use ($locale) {
                $join->on('t.legal_id', '=', 'l.id')->where('t.locale', '=', $locale);
            })
            ->selectRaw('COALESCE(t.slug, l.slug) as slug');
        if (Schema::hasColumn('legals', 'is_enabled')) {
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
}
