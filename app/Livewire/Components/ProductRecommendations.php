<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ProductRecommendations extends Component
{
    public ?int $productId = null;
    public ?int $userId = null;
    public string $type = 'related'; // related, popular, personalized
    public int $limit = 4;

    public function mount(?int $productId = null, ?int $userId = null, string $type = 'related'): void
    {
        $this->productId = $productId;
        $this->userId = $userId ?? auth()->id();
        $this->type = $type;
    }

    #[Computed]
    public function recommendations(): Collection
    {
        return match ($this->type) {
            'related' => $this->getRelatedProducts(),
            'popular' => $this->getPopularProducts(),
            'personalized' => $this->getPersonalizedRecommendations(),
            'recently_viewed' => $this->getRecentlyViewedProducts(),
            'cross_sell' => $this->getCrossSellProducts(),
            'up_sell' => $this->getUpSellProducts(),
            default => $this->getRelatedProducts(),
        };
    }

    private function getRelatedProducts(): Collection
    {
        if (!$this->productId) {
            return collect();
        }

        $product = Product::find($this->productId);
        if (!$product) {
            return collect();
        }

        // Find products in same categories
        $categoryIds = $product->categories->pluck('id');
        
        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->where('id', '!=', $this->productId)
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->inRandomOrder()
            ->limit($this->limit)
            ->get();
    }

    private function getPopularProducts(): Collection
    {
        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->withCount(['orderItems' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->where('status', 'completed')
                             ->where('created_at', '>=', now()->subMonths(3));
                });
            }])
            ->orderByDesc('order_items_count')
            ->limit($this->limit)
            ->get();
    }

    private function getPersonalizedRecommendations(): Collection
    {
        if (!$this->userId) {
            return $this->getPopularProducts();
        }

        $user = User::find($this->userId);
        if (!$user) {
            return $this->getPopularProducts();
        }

        // Get categories from user's order history
        $purchasedCategoryIds = $user->orders()
            ->with('items.product.categories')
            ->where('status', 'completed')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('product.categories')
            ->flatten()
            ->pluck('id')
            ->unique();

        if ($purchasedCategoryIds->isEmpty()) {
            return $this->getPopularProducts();
        }

        // Get products from preferred categories that user hasn't bought
        $purchasedProductIds = $user->orders()
            ->with('items')
            ->where('status', 'completed')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('product_id')
            ->unique();

        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->whereNotIn('id', $purchasedProductIds)
            ->whereHas('categories', function ($query) use ($purchasedCategoryIds) {
                $query->whereIn('categories.id', $purchasedCategoryIds);
            })
            ->inRandomOrder()
            ->limit($this->limit)
            ->get();
    }

    private function getRecentlyViewedProducts(): Collection
    {
        $viewedProductIds = session('recently_viewed', []);
        
        if (empty($viewedProductIds)) {
            return $this->getPopularProducts();
        }

        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->whereIn('id', array_slice($viewedProductIds, -$this->limit))
            ->get();
    }

    private function getCrossSellProducts(): Collection
    {
        if (!$this->productId) {
            return collect();
        }

        $product = Product::find($this->productId);
        if (!$product) {
            return collect();
        }

        // Find products frequently bought together
        $frequentlyBoughtWith = Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->where('id', '!=', $this->productId)
            ->whereHas('orderItems', function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereHas('items', function ($itemQuery) {
                        $itemQuery->where('product_id', $this->productId);
                    });
                });
            })
            ->withCount(['orderItems' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereHas('items', function ($itemQuery) {
                        $itemQuery->where('product_id', $this->productId);
                    });
                });
            }])
            ->orderByDesc('order_items_count')
            ->limit($this->limit)
            ->get();

        return $frequentlyBoughtWith->isNotEmpty() ? $frequentlyBoughtWith : $this->getRelatedProducts();
    }

    private function getUpSellProducts(): Collection
    {
        if (!$this->productId) {
            return collect();
        }

        $product = Product::find($this->productId);
        if (!$product) {
            return collect();
        }

        // Find higher-priced products in same categories
        $categoryIds = $product->categories->pluck('id');
        
        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->where('id', '!=', $this->productId)
            ->where('price', '>', $product->price)
            ->where('price', '<=', $product->price * 1.5) // Max 50% more expensive
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->orderBy('price')
            ->limit($this->limit)
            ->get();
    }

    public function trackView(): void
    {
        if ($this->productId) {
            $viewedProducts = session('recently_viewed', []);
            
            // Remove if already exists and add to front
            $viewedProducts = array_filter($viewedProducts, fn($id) => $id !== $this->productId);
            array_unshift($viewedProducts, $this->productId);
            
            // Keep only last 10 viewed products
            $viewedProducts = array_slice($viewedProducts, 0, 10);
            
            session(['recently_viewed' => $viewedProducts]);
        }
    }

    public function render(): View
    {
        return view('livewire.components.product-recommendations');
    }
}
