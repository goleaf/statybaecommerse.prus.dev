<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Computed;

#[Layout('layouts.templates.app')]
final class EnhancedHome extends Component
{
    use WithCart, WithNotifications;
    public string $searchQuery = '';
    public string $newsletterEmail = '';
    public array $filters = [
        'category' => '',
        'brand' => '',
        'price_min' => '',
        'price_max' => '',
        'in_stock' => false,
    ];

    #[Computed]
    public function featuredCollections()
    {
        return \Cache::remember(
            'home:featured_collections:' . app()->getLocale(),
            now()->addMinutes(30),
            function () {
                return Collection::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }, 'media'])
                    ->where('is_enabled', true)
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get();
            }
        );
    }

    #[Computed]
    public function featuredProducts()
    {
        $currencyCode = current_currency();
        $locale = app()->getLocale();
        
        return \Cache::remember(
            "home:featured_products:{$locale}:{$currencyCode}",
            now()->addMinutes(15),
            function () use ($currencyCode, $locale) {
                return Product::query()
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
                        'prices.currency:id,code,symbol',
                    ])
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->whereNotNull('published_at')
                    ->latest('published_at')
                    ->limit(8)
                    ->get();
            }
        );
    }

    #[Computed]
    public function newArrivals()
    {
        $currencyCode = current_currency();
        $locale = app()->getLocale();
        
        return \Cache::remember(
            "home:new_arrivals:{$locale}:{$currencyCode}",
            now()->addMinutes(15),
            function () use ($currencyCode, $locale) {
                return Product::query()
                    ->with([
                        'translations' => function ($q) use ($locale) {
                            $q->where('locale', $locale);
                        },
                        'brand:id,slug,name',
                        'media',
                        'prices' => function ($pq) use ($currencyCode) {
                            $pq->whereRelation('currency', 'code', $currencyCode);
                        },
                        'prices.currency:id,code,symbol',
                    ])
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '>=', now()->subDays(30))
                    ->latest('published_at')
                    ->limit(12)
                    ->get();
            }
        );
    }

    #[Computed]
    public function topCategories()
    {
        return \Cache::remember(
            'home:top_categories:' . app()->getLocale(),
            now()->addHour(),
            function () {
                return Category::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }, 'media'])
                    ->where('is_visible', true)
                    ->whereNull('parent_id')
                    ->withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->limit(8)
                    ->get();
            }
        );
    }

    #[Computed]
    public function topBrands()
    {
        return \Cache::remember(
            'home:top_brands:' . app()->getLocale(),
            now()->addHour(),
            function () {
                return Brand::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }, 'media'])
                    ->where('is_enabled', true)
                    ->withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->limit(12)
                    ->get();
            }
        );
    }

    public function search(): void
    {
        if (empty($this->searchQuery)) {
            return;
        }

        $this->redirect(route('search.index', [
            'q' => $this->searchQuery,
            'locale' => app()->getLocale(),
        ]));
    }

    // addToCart method now provided by WithCart trait

    public function subscribeNewsletter(): void
    {
        $this->validate([
            'newsletterEmail' => 'required|email|max:255',
        ]);

        // Here you would typically save to newsletter subscription model
        // For now, just show success message
        $this->notifySuccess(__('Successfully subscribed to newsletter!'));

        $this->reset('newsletterEmail');
    }

    public function render(): View
    {
        return view('livewire.pages.enhanced-home');
    }
}