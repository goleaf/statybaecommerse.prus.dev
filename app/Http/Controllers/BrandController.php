<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BrandController extends Controller
{
    public function show(Request $request, string $locale, string $slug): View|RedirectResponse
    {
        // Find brand by slug (check both main slug and translated slugs)
        $brand = Brand::query()
            ->with(['translations', 'media'])
            ->where('slug', $slug)
            ->where('is_enabled', true)
            ->first();

        if (! $brand) {
            // Try to find by translated slug
            $brand = Brand::query()
                ->with(['translations', 'media'])
                ->whereHas('translations', function ($query) use ($slug) {
                    $query
                        ->where('slug', $slug)
                        ->where('locale', app()->getLocale());
                })
                ->where('is_enabled', true)
                ->first();
        }

        if (! $brand) {
            abort(404);
        }

        // Get canonical slug for current locale
        $canonicalSlug = $this->getCanonicalSlug($brand);

        // If the current slug is not the canonical slug, redirect
        if ($canonicalSlug !== $slug) {
            return redirect()->route('localized.brands.show', ['locale' => $locale, 'slug' => $canonicalSlug], 301);
        }

        // Load products for this brand
        $products = $brand->products()
            ->with(['media', 'translations'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->latest()
            ->limit(12)
            ->get();

        // Get SEO data
        $seoTitle = $brand->getTranslatedSeoTitle() ?: $brand->getTranslatedName().' - '.config('app.name');
        $seoDescription = $brand->getTranslatedSeoDescription() ?: $brand->getTranslatedDescription();

        return view('brands.show', [
            'brand' => $brand,
            'products' => $products,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
        ]);
    }

    private function getCanonicalSlug(Brand $brand): string
    {
        // Get translated slug for current locale, fallback to main slug
        $translation = $brand
            ->translations()
            ->where('locale', app()->getLocale())
            ->first();

        return $translation?->slug ?: $brand->slug;
    }
}
