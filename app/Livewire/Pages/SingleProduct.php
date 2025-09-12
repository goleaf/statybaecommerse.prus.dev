<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Models\Product;
use Livewire\Component;

final class SingleProduct extends Component
{
    use WithCart;

    public Product $product;
    public int $quantity = 1;

    public function mount(Product $product): void
    {
        // Ensure product is visible and load relationships
        if (!$product->is_visible) {
            abort(404);
        }

        $product->load(['brand', 'categories', 'media', 'variants', 'reviews', 'translations']);
        $this->product = $product;
        
        // Track product view for recommendations
        $this->trackProductView();
    }

    public function trackProductView(): void
    {
        // Track in session for recently viewed products
        $viewedProducts = session('recently_viewed', []);
        
        // Remove if already exists and add to front
        $viewedProducts = array_filter($viewedProducts, fn($id) => $id !== $this->product->id);
        array_unshift($viewedProducts, $this->product->id);
        
        // Keep only last 10 viewed products
        $viewedProducts = array_slice($viewedProducts, 0, 10);
        
        session(['recently_viewed' => $viewedProducts]);
        
        // Track analytics event if analytics is enabled
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create([
                'event_type' => 'product_view',
                'event_data' => [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'product_category' => $this->product->categories->pluck('name')->join(', '),
                    'user_id' => auth()->id(),
                    'session_id' => session()->getId(),
                    'referrer' => request()->header('referer'),
                ],
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
            ]);
        }
    }

    public function addToCart(): void
    {
        // Check if product should hide add to cart
        if ($this->product->shouldHideAddToCart()) {
            $this->addError('quantity', __('frontend.product.cannot_add_to_cart'));
            return;
        }

        $this->validate([
            'quantity' => 'required|integer|min:1|max:' . $this->product->availableQuantity(),
        ]);

        // Check minimum quantity
        if ($this->quantity < $this->product->getMinimumQuantity()) {
            $this->addError('quantity', __('frontend.product.minimum_quantity_required', ['min' => $this->product->getMinimumQuantity()]));
            return;
        }

        // Call the trait method directly
        $this->addToCartTrait($this->product->id, $this->quantity);
    }

    private function addToCartTrait(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        
        if ($product->availableQuantity() < $quantity) {
            $this->addError('quantity', __('frontend.product.not_enough_stock'));
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
                    ->sum('quantity') + $quantity,
                'minimum_quantity' => $product->getMinimumQuantity(),
                'unit_price' => $product->price,
                'total_price' => $product->price * $quantity,
                'product_snapshot' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image' => $product->getFirstMediaUrl('images'),
                ],
            ]
        );
        
        $cartItem->updateTotalPrice();
        
        $this->dispatch('cart-updated');
    }

    public function getRelatedProductsProperty()
    {
        return $this->product->getRelatedProducts(4);
    }

    public function render()
    {
        return view('livewire.pages.single-product', [
            'relatedProducts' => $this->relatedProducts,
        ])->layout('components.layouts.templates.app', [
            'title' => $this->product->name,
        ]);
    }
}
