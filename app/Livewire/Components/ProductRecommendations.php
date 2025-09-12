<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
            'customers_also_bought' => $this->getCustomersAlsoBoughtProducts(),
            'trending' => $this->getTrendingProducts(),
            default => $this->getRelatedProducts(),
        };
    }

    private function getRelatedProducts(): Collection
    {
        if (! $this->productId) {
            return collect();
        }

        $product = Product::find($this->productId);
        if (! $product) {
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
        if (! $this->userId) {
            return $this->getPopularProducts();
        }

        $user = User::find($this->userId);
        if (! $user) {
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
        if (! $this->productId) {
            return collect();
        }

        $product = Product::find($this->productId);
        if (! $product) {
            return collect();
        }

        // Find products frequently bought together based on completed orders
        $frequentlyBoughtWith = Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->where('id', '!=', $this->productId)
            ->whereHas('orderItems', function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['completed', 'delivered'])
                        ->whereHas('items', function ($itemQuery) {
                            $itemQuery->where('product_id', $this->productId);
                        });
                });
            })
            ->withCount(['orderItems' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['completed', 'delivered'])
                        ->whereHas('items', function ($itemQuery) {
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
        if (! $this->productId) {
            return collect();
        }

        $product = Product::find($this->productId);
        if (! $product) {
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

    private function getCustomersAlsoBoughtProducts(): Collection
    {
        if (! $this->productId) {
            return collect();
        }

        // Get orders that contain this product
        $orderIds = Order::whereHas('items', function ($query) {
            $query->where('product_id', $this->productId);
        })
            ->whereIn('status', ['completed', 'delivered'])
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return $this->getRelatedProducts();
        }

        // Get other products from those orders
        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->where('id', '!=', $this->productId)
            ->whereHas('orderItems', function ($query) use ($orderIds) {
                $query->whereIn('order_id', $orderIds);
            })
            ->withCount(['orderItems' => function ($query) use ($orderIds) {
                $query->whereIn('order_id', $orderIds);
            }])
            ->orderByDesc('order_items_count')
            ->limit($this->limit)
            ->get();
    }

    private function getTrendingProducts(): Collection
    {
        // Products with most sales in the last 30 days
        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->whereHas('orderItems', function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['completed', 'delivered'])
                        ->where('created_at', '>=', now()->subDays(30));
                });
            })
            ->withCount(['orderItems' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereIn('status', ['completed', 'delivered'])
                        ->where('created_at', '>=', now()->subDays(30));
                });
            }])
            ->orderByDesc('order_items_count')
            ->limit($this->limit)
            ->get();
    }

    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if ($product->shouldHideAddToCart()) {
            $this->addError('cart', __('frontend.product.cannot_add_to_cart'));

            return;
        }

        if ($product->availableQuantity() < 1) {
            $this->addError('cart', __('frontend.product.not_enough_stock'));

            return;
        }

        // Create or update cart item in database
        $cartItem = \App\Models\CartItem::updateOrCreate(
            [
                'session_id' => session()->getId(),
                'product_id' => $productId,
            ],
            [
                'quantity' => \App\Models\CartItem::where('session_id', session()->getId())
                    ->where('product_id', $productId)
                    ->sum('quantity') + 1,
                'minimum_quantity' => $product->getMinimumQuantity(),
                'unit_price' => $product->price,
                'total_price' => $product->price,
                'product_snapshot' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image' => $product->getMainImage(),
                ],
            ]
        );

        $cartItem->updateTotalPrice();

        // Track recommendation click
        $this->trackRecommendationClick($productId, 'add_to_cart');

        $this->dispatch('cart-updated');
        $this->dispatch('show-success-message', message: __('frontend.cart.product_added'));
    }

    public function trackRecommendationClick(int $productId, string $action = 'click'): void
    {
        // Track analytics event if analytics is enabled
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create([
                'event_type' => 'recommendation_click',
                'event_data' => [
                    'recommended_product_id' => $productId,
                    'source_product_id' => $this->productId,
                    'recommendation_type' => $this->type,
                    'action' => $action,
                    'user_id' => $this->userId,
                    'session_id' => session()->getId(),
                    'referrer' => request()->header('referer'),
                ],
                'user_id' => $this->userId,
                'session_id' => session()->getId(),
            ]);
        }
    }

    public function trackView(): void
    {
        if ($this->productId) {
            $viewedProducts = session('recently_viewed', []);

            // Remove if already exists and add to front
            $viewedProducts = array_filter($viewedProducts, fn ($id) => $id !== $this->productId);
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
