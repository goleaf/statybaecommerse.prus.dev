<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.templates.app')]
class Home extends Component
{
    public function render(): View
    {
        $collections = \Cache::remember(
            'home:collections:' . app()->getLocale(),
            now()->addMinutes(10),
            function () {
                return Collection::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }])
                    ->where('is_enabled', true)
                    ->latest('id')
                    ->limit(3)
                    ->get(['id', 'slug', 'name']);
            }
        );

        $currencyCode = current_currency();
        $locale = app()->getLocale();
        $products = \Cache::remember(
            "home:products:{$locale}:{$currencyCode}",
            now()->addMinutes(10),
            function () use ($currencyCode, $locale) {
                return Product::query()
                    ->select(['id', 'slug', 'name', 'summary', 'brand_id', 'published_at'])
                    ->with([
                        'translations' => function ($q) use ($locale) {
                            $q->where('locale', $locale);
                        },
                        'brand:id,slug,name',
                        'brand.translations' => function ($q) use ($locale) {
                            $q->where('locale', $locale);
                        },
                        'media',
                        'prices' => function ($pq) use ($currencyCode) {
                            $pq->whereRelation('currency', 'code', $currencyCode);
                        },
                        'prices.currency:id,code',
                    ])
                    ->withCount('variants')
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderByDesc('featured')
                    ->orderByDesc('published_at')
                    ->limit(8)
                    ->get();
            }
        );

        $brands = \Cache::remember(
            'home:brands:' . $locale,
            now()->addMinutes(10),
            function () use ($locale) {
                return Brand::query()
                    ->with(['translations' => function ($q) use ($locale) {
                        $q->where('locale', $locale);
                    }, 'media'])
                    ->where('is_enabled', true)
                    ->orderBy('name')
                    ->limit(8)
                    ->get(['id', 'slug', 'name']);
            }
        );

        return view('livewire.pages.home', compact('collections', 'products', 'brands'))
            ->title(__('Home'));
    }
}
