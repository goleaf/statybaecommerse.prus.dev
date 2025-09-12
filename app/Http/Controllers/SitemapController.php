<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

final class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        $supportedLocales = config('app.supported_locales', ['lt', 'en', 'de', 'ru']);

        foreach ($supportedLocales as $locale) {
            $sitemap .= '  <sitemap>'."\n";
            $sitemap .= '    <loc>'.route('sitemap.locale', ['locale' => $locale]).'</loc>'."\n";
            $sitemap .= '    <lastmod>'.now()->toISOString().'</lastmod>'."\n";
            $sitemap .= '  </sitemap>'."\n";
        }

        $sitemap .= '</sitemapindex>';

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    public function locale(string $locale): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        // Homepage
        $sitemap .= $this->generateUrl(
            route('localized.home', ['locale' => $locale]),
            now()->toISOString(),
            'daily',
            1.0
        );

        // Categories
        $categories = Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            $sitemap .= $this->generateUrl(
                route('localized.categories.show', ['locale' => $locale, 'category' => $category->slug]),
                $category->updated_at->toISOString(),
                'weekly',
                0.8
            );
        }

        // Products
        $products = Product::where('is_active', true)->get();
        foreach ($products as $product) {
            $sitemap .= $this->generateUrl(
                route('product.show', $product->slug),
                $product->updated_at->toISOString(),
                'weekly',
                0.7
            );
        }

        // Brands
        $brands = Brand::where('is_active', true)->get();
        foreach ($brands as $brand) {
            $sitemap .= $this->generateUrl(
                route('brands.show', $brand->slug),
                $brand->updated_at->toISOString(),
                'monthly',
                0.6
            );
        }

        // Static pages
        $staticPages = [
            'about' => 'monthly',
            'contact' => 'monthly',
            'privacy' => 'yearly',
            'terms' => 'yearly',
            'shipping' => 'monthly',
            'returns' => 'monthly',
        ];

        foreach ($staticPages as $page => $frequency) {
            if (Route::has("localized.{$page}")) {
                $sitemap .= $this->generateUrl(
                    route("localized.{$page}", ['locale' => $locale]),
                    now()->toISOString(),
                    $frequency,
                    0.5
                );
            }
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    private function generateUrl(string $url, string $lastmod, string $changefreq, float $priority): string
    {
        $urlXml = '  <url>'."\n";
        $urlXml .= '    <loc>'.htmlspecialchars($url).'</loc>'."\n";
        $urlXml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
        $urlXml .= '    <changefreq>'.$changefreq.'</changefreq>'."\n";
        $urlXml .= '    <priority>'.$priority.'</priority>'."\n";

        // Add hreflang alternatives
        $supportedLocales = config('app.supported_locales', ['lt', 'en', 'de', 'ru']);
        foreach ($supportedLocales as $locale) {
            $alternateUrl = str_replace('/'.app()->getLocale().'/', '/'.$locale.'/', $url);
            $urlXml .= '    <xhtml:link rel="alternate" hreflang="'.$locale.'" href="'.htmlspecialchars($alternateUrl).'" />'."\n";
        }

        $urlXml .= '  </url>'."\n";

        return $urlXml;
    }
}
