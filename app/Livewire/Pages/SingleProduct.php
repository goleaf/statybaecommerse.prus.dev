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
    }

    public function addToCart(): void
    {
        $this->validate([
            'quantity' => 'required|integer|min:1|max:' . $this->product->stock_quantity,
        ]);

        // Call the trait method directly
        $this->addToCartTrait($this->product->id, $this->quantity);
    }

    private function addToCartTrait(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        
        if ($product->stock_quantity < $quantity) {
            $this->addError('quantity', __('Not enough stock available'));
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
        // Get related products from the same categories
        $categoryIds = $this->product->categories->pluck('id')->toArray();

        if (empty($categoryIds)) {
            return collect();
        }

        return Product::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        })
            ->where('id', '!=', $this->product->id)
            ->where('is_visible', true)
            ->with(['media', 'brand'])
            ->limit(4)
            ->get();
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
