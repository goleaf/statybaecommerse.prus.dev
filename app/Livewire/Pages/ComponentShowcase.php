<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Data\Storefront\Home\CategoryShowcaseItemData;
use App\Data\Storefront\Home\ProductListItemData;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use function route;

/**
 * ComponentShowcase
 *
 * Livewire component for ComponentShowcase with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $testInput
 * @property string $testSelect
 * @property bool   $showModal
 * @property-read Collection<int, ProductListItemData> $featuredProducts
 * @property-read Collection<int, CategoryShowcaseItemData> $categories
 * @property-read Collection<int, array{id:int,name:string,slug:string,url:string,logo_url:?string}> $brands
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
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, ProductListItemData> $products */
        $products = TagAwareCache::remember(
            CacheKeys::componentShowcaseFeaturedProducts($locale),
            now()->addMinutes(5),
            static function () use ($locale): Collection {
                return Product::query()
                    ->with([
                        'brand',
                        'media',
                        'categories',
                        'translations' => static fn ($query) => $query->where('locale', $locale),
                        'brand.translations' => static fn ($query) => $query->where('locale', $locale),
                        'categories.translations' => static fn ($query) => $query->where('locale', $locale),
                    ])
                    ->withAvg(['reviews as average_rating' => static fn ($query) => $query->where('is_approved', true)], 'rating')
                    ->withCount(['reviews' => static fn ($query) => $query->where('is_approved', true)])
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->latest('published_at')
                    ->limit(4)
                    ->get()
                    ->reject(static function (Product $product): bool {
                        return empty($product->name)
                            || ! $product->is_visible
                            || ! $product->is_featured
                            || ($product->price ?? 0) <= 0
                            || empty($product->slug);
                    })
                    ->map(static fn (Product $product): ProductListItemData => ProductListItemData::fromModel($product, $locale))
                    ->values();
            },
            [
                CacheTags::products(),
                CacheTags::brands(),
                CacheTags::locale($locale),
            ]
        );

        return collect($products)->filter(static fn ($product): bool => $product instanceof ProductListItemData)->values();
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    #[Computed]
    public function categories(): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, CategoryShowcaseItemData> $categories */
        $categories = TagAwareCache::remember(
            CacheKeys::componentShowcaseCategories($locale),
            now()->addMinutes(5),
            static function () use ($locale): Collection {
                return Category::query()
                    ->with([
                        'media',
                        'translations' => static fn ($query) => $query->where('locale', $locale),
                    ])
                    ->withCount('products')
                    ->where('is_visible', true)
                    ->latest('updated_at')
                    ->limit(3)
                    ->get()
                    ->reject(static function (Category $category): bool {
                        return empty($category->name)
                            || ! $category->is_visible
                            || empty($category->slug);
                    })
                    ->map(static fn (Category $category): CategoryShowcaseItemData => CategoryShowcaseItemData::fromModel($category, $locale))
                    ->values();
            },
            [
                CacheTags::categories(),
                CacheTags::locale($locale),
            ]
        );

        return collect($categories)->filter(static fn ($category): bool => $category instanceof CategoryShowcaseItemData)->values();
    }

    /**
     * Handle brands functionality with proper error handling.
     */
    #[Computed]
    public function brands(): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, array{id:int,name:string,slug:string,url:string,logo_url:?string}> $brands */
        $brands = TagAwareCache::remember(
            CacheKeys::componentShowcaseBrands($locale),
            now()->addMinutes(5),
            static function () use ($locale): Collection {
                return Brand::query()
                    ->with([
                        'media',
                        'translations' => static fn ($query) => $query->where('locale', $locale),
                    ])
                    ->where('is_enabled', true)
                    ->latest('updated_at')
                    ->limit(3)
                    ->get()
                    ->reject(static function (Brand $brand): bool {
                        return empty($brand->name)
                            || ! $brand->is_enabled
                            || empty($brand->slug);
                    })
                    ->map(static function (Brand $brand) use ($locale): array {
                        $name = (string) ($brand->trans('name', $locale) ?? $brand->name ?? '');
                        $slug = (string) ($brand->trans('slug', $locale) ?? $brand->slug ?? (string) $brand->getKey());

                        $logoUrl = method_exists($brand, 'getFirstMediaUrl')
                            ? ($brand->getFirstMediaUrl('logos', 'thumb') ?: $brand->getFirstMediaUrl('logos'))
                            : null;

                        return [
                            'id'       => (int) $brand->getKey(),
                            'name'     => $name,
                            'slug'     => $slug,
                            'url'      => route('localized.brands.show', [
                                'locale' => $locale,
                                'brand'  => $slug !== '' ? $slug : $brand->getKey(),
                            ]),
                            'logo_url' => $logoUrl !== '' ? $logoUrl : null,
                        ];
                    })
                    ->values();
            },
            [
                CacheTags::brands(),
                CacheTags::locale($locale),
            ]
        );

        return collect($brands)
            ->filter(static fn ($brand): bool => is_array($brand) && ($brand['name'] ?? '') !== '')
            ->values();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.component-showcase', [
            'featuredProducts' => $this->featuredProducts,
            'categories'       => $this->categories,
            'brands'           => $this->brands,
        ]);
    }
}
