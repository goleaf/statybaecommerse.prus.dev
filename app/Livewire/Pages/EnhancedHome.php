<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.templates.app')]
final class EnhancedHome extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public function subscribeNewsletter(): void
    {
        $this->validate();

        // TODO: Implement newsletter subscription logic
        session()->flash('success', __('translations.newsletter_subscribed'));
        $this->reset('email');
    }

    public function navigateToCategory(string $slug): void
    {
        $this->redirect(route('categories.show', [
            'locale' => app()->getLocale(),
            'slug' => $slug
        ]));
    }

    public function navigateToBrand(string $slug): void
    {
        $this->redirect(route('brands.show', [
            'locale' => app()->getLocale(),
            'slug' => $slug
        ]));
    }

    public function render(): View
    {
        $locale = app()->getLocale();

        // Featured categories (top-level only)
        $categories = \Cache::remember(
            "home:categories:{$locale}",
            now()->addMinutes(30),
            function () use ($locale) {
                return Category::query()
                    ->with(['translations' => function ($q) use ($locale) {
                        $q->where('locale', $locale);
                    }, 'media'])
                    ->whereNull('parent_id')
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get(['id', 'slug', 'name', 'sort_order']);
            }
        );

        // Featured products
        $products = \Cache::remember(
            "home:featured_products:{$locale}",
            now()->addMinutes(15),
            function () use ($locale) {
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
                        'categories:id,name,slug'
                    ])
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderByDesc('published_at')
                    ->limit(8)
                    ->get();
            }
        );

        // Top brands
        $brands = \Cache::remember(
            "home:brands:{$locale}",
            now()->addMinutes(60),
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

        // Featured collections
        $collections = \Cache::remember(
            "home:collections:{$locale}",
            now()->addMinutes(30),
            function () use ($locale) {
                return Collection::query()
                    ->with(['translations' => function ($q) use ($locale) {
                        $q->where('locale', $locale);
                    }, 'media'])
                    ->where('is_enabled', true)
                    ->orderBy('sort_order')
                    ->limit(3)
                    ->get(['id', 'slug', 'name', 'sort_order']);
            }
        );

        return view('livewire.pages.enhanced-home', compact('categories', 'products', 'brands', 'collections'))
            ->title(__('translations.home_title'));
    }
}
