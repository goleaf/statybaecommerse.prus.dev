<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Contracts\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        abort_if(! shopper_feature_enabled('brand'), 404);
        $sort = request('sort');
        $query = Brand::query()->where('is_enabled', true);
        if ($sort === 'name_desc') {
            $query->orderByDesc('name');
        } else {
            $query->orderBy('name');
        }
        $brands = $query->paginate(24)->appends(['sort' => $sort]);

        return view('brands.index', compact('brands'));
    }

    public function show(string $slug): View
    {
        abort_if(! shopper_feature_enabled('brand'), 404);
        $locale = app()->getLocale();
        $brand = Brand::query()
            ->select(['id', 'slug', 'name'])
            ->where(function ($q) use ($slug, $locale) {
                $q
                    ->where('slug', $slug)
                    ->orWhereExists(function ($sq) use ($slug, $locale) {
                        $sq
                            ->selectRaw('1')
                            ->from('sh_brand_translations as t')
                            ->whereColumn('t.brand_id', 'sh_brands.id')
                            ->where('t.locale', $locale)
                            ->where('t.slug', $slug);
                    });
            })
            ->firstOrFail();

        // Canonical redirect if slug does not match the current-locale canonical slug
        $canonical = $brand->translations()->where('locale', $locale)->value('slug') ?: $brand->slug;
        if ($canonical && $canonical !== $slug) {
            return redirect()->route('brand.show', ['locale' => $locale, 'slug' => $canonical], 301);
        }

        $products = $brand->products()->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now())->paginate(24);

        return view('brands.show', compact('brand', 'products'));
    }
}
