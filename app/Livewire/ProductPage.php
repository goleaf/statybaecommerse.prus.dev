<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Brand;
use Livewire\Component;
use Illuminate\Support\Collection;

final class ProductPage extends Component
{
    public Product $product;
    public Collection $relatedProducts;
    public Collection $recentlyViewed;
    public bool $showImageModal = false;
    public int $selectedImageIndex = 0;
    public string $activeTab = 'description';

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->loadRelatedProducts();
        $this->loadRecentlyViewed();
        $this->trackProductView();
    }

    public function loadRelatedProducts(): void
    {
        $this->relatedProducts = Product::where('id', '!=', $this->product->id)
            ->whereHas('categories', function ($query) {
                $query->whereIn('categories.id', $this->product->categories->pluck('id'));
            })
            ->orWhere('brand_id', $this->product->brand_id)
            ->enabled()
            ->visible()
            ->published()
            ->with(['variants', 'brand', 'categories'])
            ->limit(4)
            ->get();
    }

    public function loadRecentlyViewed(): void
    {
        // Load recently viewed products from session
        $recentlyViewedIds = session('recently_viewed', []);
        
        if (!empty($recentlyViewedIds)) {
            $this->recentlyViewed = Product::whereIn('id', $recentlyViewedIds)
                ->where('id', '!=', $this->product->id)
                ->enabled()
                ->visible()
                ->published()
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
        $recentlyViewed = array_filter($recentlyViewed, fn($id) => $id !== $this->product->id);
        
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
        $images = collect();
        
        // Get main product images
        if ($this->product->hasMedia('images')) {
            $images = $images->merge($this->product->getMedia('images'));
        }
        
        // Get variant images
        $variantImages = $this->product->variants()
            ->with('images')
            ->get()
            ->pluck('images')
            ->flatten();
        
        $images = $images->merge($variantImages);
        
        return $images->unique('id');
    }

    public function getProductPriceRange(): array
    {
        $variants = $this->product->variants()->enabled()->get();
        
        if ($variants->isEmpty()) {
            return [
                'min' => $this->product->price ?? 0,
                'max' => $this->product->price ?? 0,
            ];
        }
        
        $prices = $variants->pluck('final_price');
        
        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    public function getProductStockStatus(): string
    {
        $variants = $this->product->variants()->enabled()->get();
        
        if ($variants->isEmpty()) {
            return $this->product->is_in_stock ? 'in_stock' : 'out_of_stock';
        }
        
        $inStockVariants = $variants->filter(fn($variant) => $variant->isAvailableForPurchase());
        
        if ($inStockVariants->isEmpty()) {
            return 'out_of_stock';
        }
        
        $lowStockVariants = $inStockVariants->filter(fn($variant) => $variant->is_low_stock);
        
        if ($lowStockVariants->count() === $inStockVariants->count()) {
            return 'low_stock';
        }
        
        return 'in_stock';
    }

    public function getProductStockMessage(): string
    {
        $status = $this->getProductStockStatus();
        
        return match ($status) {
            'in_stock' => __('products.messages.in_stock'),
            'low_stock' => __('products.messages.low_stock'),
            'out_of_stock' => __('products.messages.out_of_stock'),
            default => __('products.messages.unknown_stock'),
        };
    }

    public function getProductCategories(): Collection
    {
        return $this->product->categories()->enabled()->visible()->get();
    }

    public function getProductBrand(): ?Brand
    {
        return $this->product->brand;
    }

    public function getProductVariants(): Collection
    {
        return $this->product->variants()
            ->with(['attributes.attribute', 'images'])
            ->enabled()
            ->orderBy('position')
            ->get();
    }

    public function getProductAttributes(): Collection
    {
        return $this->product->attributes()
            ->with('values')
            ->enabled()
            ->orderBy('sort_order')
            ->get();
    }

    public function getProductReviews(): Collection
    {
        // Assuming you have a reviews relationship
        return $this->product->reviews ?? collect();
    }

    public function getProductRating(): float
    {
        $reviews = $this->getProductReviews();
        
        if ($reviews->isEmpty()) {
            return 0.0;
        }
        
        return $reviews->avg('rating') ?? 0.0;
    }

    public function getProductReviewsCount(): int
    {
        return $this->getProductReviews()->count();
    }

    public function shareProduct(): void
    {
        // Implement sharing functionality
        $this->dispatch('share-product', [
            'url' => route('products.show', $this->product),
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
        return view('livewire.product-page')
            ->layout('layouts.app');
    }
}
