<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\LazyCollection;
/**
 * SitemapController
 * 
 * HTTP controller handling SitemapController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class SitemapController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @return Response
     */
    public function index(): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $supportedLocales = is_array(config('app.supported_locales')) ? config('app.supported_locales') : ['lt', 'en', 'de', 'ru'];
        foreach ($supportedLocales as $locale) {
            $sitemap .= '  <sitemap>' . "\n";
            $sitemap .= '    <loc>' . route('sitemap.locale', ['locale' => $locale]) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . now()->toISOString() . '</lastmod>' . "\n";
            $sitemap .= '  </sitemap>' . "\n";
        }
        $sitemap .= '</sitemapindex>';
        return response($sitemap, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
    /**
     * Handle locale functionality with proper error handling.
     * @param string $locale
     * @return Response
     */
    public function locale(string $locale): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        // Homepage
        $sitemap .= $this->generateUrl(route('localized.home', ['locale' => $locale]), now()->toISOString(), 'daily', 1.0);
        // Categories with timeout protection
        $timeout = now()->addSeconds(30);
        // 30 second timeout for sitemap generation
        $categories = Category::where('is_active', true)->get()->skipWhile(function ($category) {
            // Skip categories that are not properly configured for sitemap
            return empty($category->name) || !$category->is_active || empty($category->slug);
        });
        LazyCollection::make($categories)->takeUntilTimeout($timeout)->each(function ($category) use (&$sitemap, $locale) {
            $sitemap .= $this->generateUrl(route('localized.categories.show', ['locale' => $locale, 'category' => $category->slug]), $category->updated_at->toISOString(), 'weekly', 0.8);
        });
        // Products with timeout protection
        $products = Product::where('is_active', true)->get()->skipWhile(function ($product) {
            // Skip products that are not properly configured for sitemap
            return empty($product->name) || !$product->is_active || empty($product->slug) || $product->price <= 0;
        });
        LazyCollection::make($products)->takeUntilTimeout($timeout)->each(function ($product) use (&$sitemap) {
            $sitemap .= $this->generateUrl(route('product.show', $product->slug), $product->updated_at->toISOString(), 'weekly', 0.7);
        });
        // Brands with timeout protection
        $brands = Brand::where('is_active', true)->get()->skipWhile(function ($brand) {
            // Skip brands that are not properly configured for sitemap
            return empty($brand->name) || !$brand->is_active || empty($brand->slug);
        });
        LazyCollection::make($brands)->takeUntilTimeout($timeout)->each(function ($brand) use (&$sitemap) {
            $sitemap .= $this->generateUrl(localized_route('brands.show', $brand->slug), $brand->updated_at->toISOString(), 'monthly', 0.6);
        });
        // Static pages
        $staticPages = ['about' => 'monthly', 'contact' => 'monthly', 'privacy' => 'yearly', 'terms' => 'yearly', 'shipping' => 'monthly', 'returns' => 'monthly'];
        foreach ($staticPages as $page => $frequency) {
            if (Route::has("localized.{$page}")) {
                $sitemap .= $this->generateUrl(route("localized.{$page}", ['locale' => $locale]), now()->toISOString(), $frequency, 0.5);
            }
        }
        $sitemap .= '</urlset>';
        return response($sitemap, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
    /**
     * Handle generateUrl functionality with proper error handling.
     * @param string $url
     * @param string $lastmod
     * @param string $changefreq
     * @param float $priority
     * @return string
     */
    private function generateUrl(string $url, string $lastmod, string $changefreq, float $priority): string
    {
        $urlXml = '  <url>' . "\n";
        $urlXml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
        $urlXml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        $urlXml .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        $urlXml .= '    <priority>' . $priority . '</priority>' . "\n";
        // Add hreflang alternatives
        $supportedLocales = is_array(config('app.supported_locales')) ? config('app.supported_locales') : ['lt', 'en', 'de', 'ru'];
        foreach ($supportedLocales as $locale) {
            $alternateUrl = str_replace('/' . app()->getLocale() . '/', '/' . $locale . '/', $url);
            $urlXml .= '    <xhtml:link rel="alternate" hreflang="' . $locale . '" href="' . htmlspecialchars($alternateUrl) . '" />' . "\n";
        }
        $urlXml .= '  </url>' . "\n";
        return $urlXml;
    }
}