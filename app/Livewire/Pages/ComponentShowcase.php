<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ComponentShowcase
 *
 * Livewire component for ComponentShowcase with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $testInput
 * @property string $testSelect
 * @property bool   $showModal
 * @property-read EloquentCollection<int, Product> $featuredProducts
 * @property-read EloquentCollection<int, Category> $categories
 * @property-read EloquentCollection<int, Brand> $brands
 */
#[Layout('components.layouts.base')]
final class ComponentShowcase extends Component
{
    use WithNotifications;

    public string $testInput = '';

    public string $testSelect = '';

    public bool $showModal = false;

    /**
     * Handle testNotification functionality with proper error handling.
     */
    public function testNotification(string $type): void
    {
        match ($type) {
            'success' => $this->notifySuccess('Success notification!', 'Success'),
            'error'   => $this->notifyError('Error notification!', 'Error'),
            'warning' => $this->notifyWarning('Warning notification!', 'Warning'),
            'info'    => $this->notifyInfo('Info notification!', 'Info'),
            default   => $this->notifyInfo('Info notification!', 'Info'),
        };
    }

    /**
     * Handle toggleModal functionality with proper error handling.
     */
    public function toggleModal(): void
    {
        $this->showModal = ! $this->showModal;
    }

    /**
     * Handle featuredProducts functionality with proper error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): EloquentCollection
    {
        $locale = app()->getLocale();

        /** @var EloquentCollection<int, Product> $products */
        $products = TagAwareCache::remember(
            CacheKeys::componentShowcaseFeaturedProducts($locale),
            now()->addMinutes(5),
            static function (): EloquentCollection {
                return Product::query()
                    ->with(['brand', 'media', 'prices'])
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->latest('published_at')
                    ->limit(4)
                    ->get();
            },
            [
                CacheTags::products(),
                CacheTags::brands(),
                CacheTags::locale($locale),
            ]
        );

        return $products->reject(static function (Product $product): bool {
            // Skip products that are not properly configured for showcase display.
            return empty($product->name) || ! $product->is_visible || ! $product->is_featured || ($product->price ?? 0) <= 0 || empty($product->slug);
        })->values();
    }

    /**
     * Handle categories functionality with proper error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    #[Computed]
    public function categories(): EloquentCollection
    {
        $locale = app()->getLocale();

        /** @var EloquentCollection<int, Category> $categories */
        $categories = TagAwareCache::remember(
            CacheKeys::componentShowcaseCategories($locale),
            now()->addMinutes(5),
            static function (): EloquentCollection {
                return Category::query()
                    ->where('is_visible', true)
                    ->latest('updated_at')
                    ->limit(3)
                    ->get();
            },
            [
                CacheTags::categories(),
                CacheTags::locale($locale),
            ]
        );

        return $categories->reject(static function (Category $category): bool {
            // Skip categories that are not properly configured for showcase display.
            return empty($category->name) || ! $category->is_visible || empty($category->slug);
        })->values();
    }

    /**
     * Handle brands functionality with proper error handling.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Brand>
     */
    #[Computed]
    public function brands(): EloquentCollection
    {
        $locale = app()->getLocale();

        /** @var EloquentCollection<int, Brand> $brands */
        $brands = TagAwareCache::remember(
            CacheKeys::componentShowcaseBrands($locale),
            now()->addMinutes(5),
            static function (): EloquentCollection {
                return Brand::query()
                    ->where('is_enabled', true)
                    ->latest('updated_at')
                    ->limit(3)
                    ->get();
            },
            [
                CacheTags::brands(),
                CacheTags::locale($locale),
            ]
        );

        return $brands->reject(static function (Brand $brand): bool {
            // Skip brands that are not properly configured for showcase display.
            return empty($brand->name) || ! $brand->is_enabled || empty($brand->slug);
        })->values();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.component-showcase', ['featuredProducts' => $this->featuredProducts, 'categories' => $this->categories, 'brands' => $this->brands]);
    }
}
