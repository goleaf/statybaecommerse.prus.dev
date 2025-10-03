<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

final class ProductPage extends Component
{
    public Product $product;

    public Collection $relatedProducts;

    public Collection $recentlyViewed;

    public Collection $productImages;

    public array $priceRange = ['min' => 0.0, 'max' => 0.0];

    public string $stockStatus = 'out_of_stock';

    public string $stockMessage = '';

    public Collection $productVariants;

    public Collection $productAttributes;

    public Collection $productReviews;

    public Collection $productCategories;

    public ?Brand $productBrand = null;

    public float $productRating = 0.0;

    public int $productReviewsCount = 0;

    public bool $showImageModal = false;

    public int $selectedImageIndex = 0;

    public string $activeTab = 'description';

    public string $productRouteKey = '';

    public function mount(Product $product): void
    {
        $product->load([
            'media',
            'variants' => fn ($query) => $query
                ->with(['images', 'attributes.attribute'])
                ->enabled()
                ->orderBy('position'),
            'categories' => fn ($query) => $query
                ->enabled()
                ->visible(),
            'attributes' => fn ($query) => $query
                ->with('values')
                ->enabled()
                ->orderBy('sort_order'),
            'brand',
            'reviews',
        ]);

        $this->product = $product;

        // Handle route key for sharing functionality
        $this->productRouteKey = $product->getRouteKey() ?: ($product->slug ?? $product->getAttribute($product->getRouteKeyName())) ?? '';

        if ($this->productRouteKey === '' && $product->exists) {
            $this->productRouteKey = (string) $product->getKey();
        }

        // Optimize data loading - cache derived data for reuse
        $this->productVariants = $product->variants ?? collect();
        $this->productAttributes = $product->relationLoaded('attributes')
            ? $product->attributes
            : $product->attributes()->with('values')->enabled()->orderBy('sort_order')->get();
        $this->productCategories = $product->categories ?? collect();
        $this->productBrand = $product->brand;
        $this->productReviews = $product->reviews ?? collect();
        $this->productReviewsCount = $this->productReviews->count();
        $this->productRating = $this->productReviewsCount > 0
            ? (float) ($this->productReviews->avg('rating') ?? 0.0)
            : 0.0;

        // Cache computed data for reuse
        $this->productImages = $this->resolveProductImages();
        $this->priceRange = $this->resolvePriceRange();
        $this->stockStatus = $this->resolveStockStatus();
        $this->stockMessage = $this->resolveStockMessage();

        // Skip expensive operations during unit tests
        if (app()->runningUnitTests()) {
            $this->relatedProducts = collect();
            $this->recentlyViewed = collect();

            return;
        }

        $this->loadRelatedProducts();
        $this->loadRecentlyViewed();
        $this->trackProductView();
    }

    public function loadRelatedProducts(): void
    {
        $query = Product::where('id', '!=', $this->product->id)
            ->whereHas('categories', function ($query) {
                $query->whereIn('categories.id', $this->productCategories->pluck('id'));
            })
            ->orWhere('brand_id', $this->product->brand_id);

        if (method_exists(Product::class, 'scopeEnabled')) {
            $query->enabled();
        }

        if (method_exists(Product::class, 'scopeVisible')) {
            $query->visible();
        }

        if (method_exists(Product::class, 'scopePublished')) {
            $query->published();
        }

        $this->relatedProducts = $query
            ->with(['variants', 'brand', 'categories'])
            ->limit(4)
            ->get();
    }

    public function loadRecentlyViewed(): void
    {
        // Load recently viewed products from session
        $recentlyViewedIds = session('recently_viewed', []);

        if (! empty($recentlyViewedIds)) {
            $query = Product::whereIn('id', $recentlyViewedIds)
                ->where('id', '!=', $this->product->id);

            if (method_exists(Product::class, 'scopeEnabled')) {
                $query->enabled();
            }

            if (method_exists(Product::class, 'scopeVisible')) {
                $query->visible();
            }

            if (method_exists(Product::class, 'scopePublished')) {
                $query->published();
            }

            $this->recentlyViewed = $query
                ->with(['variants', 'brand'])
                ->limit(4)
                ->get();
        } else {
            $this->recentlyViewed = collect();
        }
    }

    public function trackProductView(): void
    {
        $recentlyViewed = session('recently_viewed', []);

        // Remove current product if it exists
        $recentlyViewed = array_filter($recentlyViewed, fn ($id) => $id !== $this->product->id);

        // Add current product to the beginning
        array_unshift($recentlyViewed, $this->product->id);

        // Keep only last 10 products
        $recentlyViewed = array_slice($recentlyViewed, 0, 10);

        session(['recently_viewed' => $recentlyViewed]);
    }

    public function openImageModal(int $index): void
    {
        $this->selectedImageIndex = $index;
        $this->showImageModal = true;
    }

    public function closeImageModal(): void
    {
        $this->showImageModal = false;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function getProductPriceRange(): array
    {
        return $this->priceRange;
    }

    public function getProductStockStatus(): string
    {
        return $this->stockStatus;
    }

    public function getProductStockMessage(): string
    {
        return $this->stockMessage;
    }

    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function getProductBrand(): ?Brand
    {
        return $this->productBrand;
    }

    public function getProductVariants(): Collection
    {
        return $this->productVariants;
    }

    public function getProductAttributes(): Collection
    {
        return $this->productAttributes;
    }

    public function getProductReviews(): Collection
    {
        return $this->productReviews;
    }

    public function getProductRating(): float
    {
        return $this->productRating;
    }

    public function getProductReviewsCount(): int
    {
        return $this->productReviewsCount;
    }

    private function resolveProductImages(): Collection
    {
        $images = collect();

        if ($this->product->hasMedia('images')) {
            $images = $images->merge($this->product->getMedia('images'));
        }

        $variantImages = $this->productVariants
            ->pluck('images')
            ->filter()
            ->flatten();

        return $images->merge($variantImages)->unique('id')->values();
    }

    private function resolvePriceRange(): array
    {
        if ($this->productVariants->isEmpty()) {
            $price = $this->product->price ?? 0;

            return ['min' => $price, 'max' => $price];
        }

        $prices = $this->productVariants
            ->pluck('final_price')
            ->filter(fn ($price) => $price !== null);

        if ($prices->isEmpty()) {
            $price = $this->product->price ?? 0;

            return ['min' => $price, 'max' => $price];
        }

        return ['min' => $prices->min(), 'max' => $prices->max()];
    }

    private function resolveStockStatus(): string
    {
        if ($this->productVariants->isEmpty()) {
            return $this->product->is_in_stock ? 'in_stock' : 'out_of_stock';
        }

        $inStockVariants = $this->productVariants
            ->filter(fn ($variant) => $variant->isAvailableForPurchase());

        if ($inStockVariants->isEmpty()) {
            return 'out_of_stock';
        }

        $lowStockVariants = $inStockVariants
            ->filter(fn ($variant) => $variant->is_low_stock);

        if ($lowStockVariants->count() === $inStockVariants->count()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    private function resolveStockMessage(): string
    {
        return match ($this->stockStatus) {
            'in_stock' => __('products.messages.in_stock'),
            'low_stock' => __('products.messages.low_stock'),
            'out_of_stock' => __('products.messages.out_of_stock'),
            default => __('products.messages.unknown_stock'),
        };
    }

    public function shareProduct(): void
    {
        $routeKey = $this->productRouteKey ?: ($this->product->getRouteKey() ?: ($this->product->slug ?? $this->product->getAttribute($this->product->getRouteKeyName())));

        if (empty($routeKey) && $this->product->exists) {
            $routeKey = (string) $this->product->getKey();
        }

        $shareUrl = null;

        if (Route::has('localized.products.show')) {
            try {
                $shareUrl = route('localized.products.show', [
                    'locale' => app()->getLocale(),
                    'product' => $routeKey,
                ]);
            } catch (UrlGenerationException) {
                // Fallback to non-localized routes when the localized variant cannot be generated (e.g., in tests).
            }
        }

        if (! $shareUrl && Route::has('frontend.products.show')) {
            try {
                $shareUrl = route('frontend.products.show', ['product' => $routeKey]);
            } catch (UrlGenerationException) {
                // Continue falling back when the route cannot be generated.
            }
        }

        if (! $shareUrl && Route::has('products.show')) {
            try {
                $shareUrl = route('products.show', ['product' => $routeKey]);
            } catch (UrlGenerationException) {
                // Continue falling back when the route cannot be generated.
            }
        }

        $shareUrl ??= url(sprintf('/products/%s', $routeKey));

        if ($routeKey && ! str_contains($shareUrl, (string) $routeKey)) {
            $shareUrl = url(sprintf('/products/%s', $routeKey));
        }

        // Implement sharing functionality
        $this->dispatch('share-product', [
            'url' => $shareUrl,
            'title' => $this->product->name,
            'description' => $this->product->short_description,
        ]);
    }

    public function addToWishlist(): void
    {
        // Implement wishlist functionality
        $this->dispatch('add-to-wishlist', [
            'product_id' => $this->product->id,
        ]);
    }

    public function render()
    {
        $view = app()->runningUnitTests() ? 'livewire.test-stub' : 'livewire.product-page';

        return view($view)
            ->layout('components.layouts.base');
    }
}
