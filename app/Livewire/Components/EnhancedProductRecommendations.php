<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final /**
 * EnhancedProductRecommendations
 * 
 * Enhanced Livewire component using the new recommendation system.
 */
class EnhancedProductRecommendations extends Component
{
    public ?int $productId = null;
    public ?int $userId = null;
    public string $blockName = 'related_products';
    public int $limit = 4;
    public array $context = [];
    public bool $showTitle = true;
    public string $title = '';
    public bool $trackInteractions = true;

    protected RecommendationService $recommendationService;

    public function mount(
        ?int $productId = null,
        ?int $userId = null,
        string $blockName = 'related_products',
        int $limit = 4,
        array $context = [],
        bool $showTitle = true,
        string $title = '',
        bool $trackInteractions = true
    ): void {
        $this->productId = $productId;
        $this->userId = $userId ?? auth()->id();
        $this->blockName = $blockName;
        $this->limit = $limit;
        $this->context = $context;
        $this->showTitle = $showTitle;
        $this->title = $title ?: $this->getDefaultTitle();
        $this->trackInteractions = $trackInteractions;

        $this->recommendationService = app(RecommendationService::class);
    }

    #[Computed]
    public function recommendations(): Collection
    {
        try {
            $user = $this->userId ? User::find($this->userId) : null;
            $product = $this->productId ? Product::find($this->productId) : null;

            $recommendations = $this->recommendationService->getRecommendations(
                $this->blockName,
                $user,
                $product,
                array_merge($this->context, [
                    'limit' => $this->limit,
                    'component' => 'enhanced_product_recommendations',
                ])
            );

            return $recommendations->take($this->limit);

        } catch (\Exception $e) {
            \Log::error('Enhanced Product Recommendations Error', [
                'block_name' => $this->blockName,
                'product_id' => $this->productId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            // Fallback to basic recommendations
            return $this->getFallbackRecommendations();
        }
    }

    public function addToCart(int $productId): void
    {
        try {
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

            // Track recommendation interaction
            if ($this->trackInteractions && $this->userId) {
                $user = User::find($this->userId);
                if ($user) {
                    $this->recommendationService->trackUserInteraction(
                        $user,
                        $product,
                        'add_to_cart'
                    );
                }
            }

            $this->dispatch('cart-updated');
            $this->dispatch('show-success-message', message: __('frontend.cart.product_added'));

        } catch (\Exception $e) {
            $this->addError('cart', __('frontend.product.add_to_cart_error'));
        }
    }

    public function trackView(int $productId): void
    {
        if (!$this->trackInteractions || !$this->userId) {
            return;
        }

        try {
            $user = User::find($this->userId);
            $product = Product::find($productId);

            if ($user && $product) {
                $this->recommendationService->trackUserInteraction(
                    $user,
                    $product,
                    'view'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to track product view', [
                'user_id' => $this->userId,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function trackClick(int $productId): void
    {
        if (!$this->trackInteractions || !$this->userId) {
            return;
        }

        try {
            $user = User::find($this->userId);
            $product = Product::find($productId);

            if ($user && $product) {
                $this->recommendationService->trackUserInteraction(
                    $user,
                    $product,
                    'click'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to track product click', [
                'user_id' => $this->userId,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getDefaultTitle(): string
    {
        return match ($this->blockName) {
            'related_products' => __('frontend.recommendations.related_products'),
            'you_might_also_like' => __('frontend.recommendations.you_might_also_like'),
            'similar_products' => __('frontend.recommendations.similar_products'),
            'popular_products' => __('frontend.recommendations.popular_products'),
            'trending_products' => __('frontend.recommendations.trending_products'),
            'customers_also_bought' => __('frontend.recommendations.customers_also_bought'),
            'cross_sell' => __('frontend.recommendations.cross_sell'),
            'up_sell' => __('frontend.recommendations.up_sell'),
            'personalized' => __('frontend.recommendations.personalized'),
            'recently_viewed' => __('frontend.recommendations.recently_viewed'),
            default => __('frontend.recommendations.recommended_products'),
        };
    }

    private function getFallbackRecommendations(): Collection
    {
        // Simple fallback to category-based recommendations
        if (!$this->productId) {
            return Product::query()
                ->with(['media', 'brand'])
                ->where('is_visible', true)
                ->inRandomOrder()
                ->limit($this->limit)
                ->get();
        }

        $product = Product::find($this->productId);
        if (!$product) {
            return collect();
        }

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

    public function render(): View
    {
        return view('livewire.components.enhanced-product-recommendations');
    }
}
