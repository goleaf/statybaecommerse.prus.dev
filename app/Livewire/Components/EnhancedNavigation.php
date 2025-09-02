<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Collection;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class EnhancedNavigation extends Component
{
    public bool $mobileMenuOpen = false;
    public bool $searchOpen = false;
    public string $searchQuery = '';

    #[Computed]
    public function mainCategories()
    {
        return \Cache::remember(
            'nav:main_categories:' . app()->getLocale(),
            now()->addHour(),
            function () {
                return Category::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }, 'children.translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }])
                    ->where('is_visible', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->limit(8)
                    ->get();
            }
        );
    }

    #[Computed]
    public function featuredBrands()
    {
        return \Cache::remember(
            'nav:featured_brands:' . app()->getLocale(),
            now()->addHour(),
            function () {
                return Brand::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }])
                    ->where('is_enabled', true)
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get();
            }
        );
    }

    #[Computed]
    public function featuredCollections()
    {
        return \Cache::remember(
            'nav:featured_collections:' . app()->getLocale(),
            now()->addHour(),
            function () {
                return Collection::query()
                    ->with(['translations' => function ($q) {
                        $q->where('locale', app()->getLocale());
                    }])
                    ->where('is_enabled', true)
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->limit(4)
                    ->get();
            }
        );
    }

    public function toggleMobileMenu(): void
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }

    public function toggleSearch(): void
    {
        $this->searchOpen = !$this->searchOpen;
        if ($this->searchOpen) {
            $this->dispatch('focus-search');
        }
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

    public function render(): View
    {
        return view('livewire.components.enhanced-navigation');
    }
}
