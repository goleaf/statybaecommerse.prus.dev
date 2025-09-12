<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function show(Request $request, string $locale, string $slug): RedirectResponse
    {
        // Find brand by slug (check both main slug and translated slugs)
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('is_enabled', true)
            ->first();

        if (!$brand) {
            // Try to find by translated slug
            $brand = Brand::query()
                ->whereHas('translations', function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->where('locale', app()->getLocale());
                })
                ->where('is_enabled', true)
                ->first();
        }

        if (!$brand) {
            // Debug: Log what we're looking for
            \Log::info('Brand not found', [
                'slug' => $slug,
                'locale' => app()->getLocale(),
                'all_brands' => Brand::all()->pluck('slug'),
                'all_translations' => \App\Models\Translations\BrandTranslation::all()->pluck('slug')
            ]);
            abort(404);
        }

        // Get canonical slug for current locale
        $canonicalSlug = $this->getCanonicalSlug($brand);
        
        // If the current slug is not the canonical slug, redirect
        if ($canonicalSlug !== $slug) {
            return redirect()->route('localized.brands.show', ['locale' => $locale, 'slug' => $canonicalSlug], 301);
        }

        // If we reach here, the slug is canonical, but we still need to redirect
        // to the localized route to maintain consistency
        return redirect()->route('localized.brands.show', ['locale' => $locale, 'slug' => $canonicalSlug], 301);
    }

    private function getCanonicalSlug(Brand $brand): string
    {
        // Get translated slug for current locale, fallback to main slug
        $translation = $brand->translations()
            ->where('locale', app()->getLocale())
            ->first();

        return $translation?->slug ?: $brand->slug;
    }
}