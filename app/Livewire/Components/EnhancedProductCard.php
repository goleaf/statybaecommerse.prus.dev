<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class EnhancedProductCard extends Component
{
    public Product $product;
    public bool $showQuickView = false;
    public bool $showVariants = false;
    public ?ProductVariant $selectedVariant = null;
    public int $quantity = 1;
    public bool $isWishlisted = false;
    public bool $showCompare = false;

    #[Validate('required|integer|min:1')]
    public int $quickViewQuantity = 1;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->selectedVariant = $product->variants->first();
        $this->checkWishlistStatus();
    }

    public function toggleQuickView(): void
    {
        $this->showQuickView = !$this->showQuickView;
        if ($this->showQuickView) {
            $this->dispatch('open-quick-view', productId: $this->product->id);
        }
    }

    public function selectVariant(int $variantId): void
    {
        $this->selectedVariant = $this->product->variants->find($variantId);
        $this->showVariants = false;
    }

    public function addToCart(): void
    {
        if (!$this->selectedVariant) {
            session()->flash('error', __('Please select a variant'));
            return;
        }

        if ($this->selectedVariant->stock_quantity < $this->quantity) {
            session()->flash('error', __('Insufficient stock'));
            return;
        }

        // Add to cart logic
        $cartItems = session()->get('cart', []);
        $key = $this->selectedVariant->id;

        if (isset($cartItems[$key])) {
            $cartItems[$key]['quantity'] += $this->quantity;
        } else {
            $cartItems[$key] = [
                'product_id' => $this->product->id,
                'variant_id' => $this->selectedVariant->id,
                'quantity' => $this->quantity,
                'price' => $this->selectedVariant->price,
                'name' => $this->product->name,
                'image' => $this->product->getFirstMediaUrl('gallery'),
            ];
        }

        session()->put('cart', $cartItems);
        $this->dispatch('cart-updated');
        session()->flash('success', __('Product added to cart'));
    }

    public function quickAddToCart(): void
    {
        $this->quantity = $this->quickViewQuantity;
        $this->addToCart();
        $this->showQuickView = false;
    }

    public function toggleWishlist(): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $user = auth()->user();
        
        if ($this->isWishlisted) {
            // Remove from wishlist
            $user->wishlist()->detach($this->product->id);
            $this->isWishlisted = false;
            session()->flash('success', __('Removed from wishlist'));
        } else {
            // Add to wishlist
            $user->wishlist()->attach($this->product->id);
            $this->isWishlisted = true;
            session()->flash('success', __('Added to wishlist'));
        }

        $this->dispatch('wishlist-updated');
    }

    public function addToCompare(): void
    {
        $compareItems = session()->get('compare', []);
        
        if (count($compareItems) >= 4) {
            session()->flash('error', __('You can compare maximum 4 products'));
            return;
        }

        if (!in_array($this->product->id, $compareItems)) {
            $compareItems[] = $this->product->id;
            session()->put('compare', $compareItems);
            $this->dispatch('compare-updated');
            session()->flash('success', __('Added to comparison'));
        }
    }

    public function getDiscountPercentageProperty(): ?float
    {
        if (!$this->selectedVariant || !$this->selectedVariant->compare_price) {
            return null;
        }

        return round((($this->selectedVariant->compare_price - $this->selectedVariant->price) / $this->selectedVariant->compare_price) * 100);
    }

    public function getAverageRatingProperty(): float
    {
        return $this->product->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    public function getReviewCountProperty(): int
    {
        return $this->product->reviews()->where('is_approved', true)->count();
    }

    private function checkWishlistStatus(): void
    {
        if (auth()->check()) {
            $this->isWishlisted = auth()->user()->wishlist()->where('product_id', $this->product->id)->exists();
        }
    }

    public function render(): View
    {
        return view('livewire.components.enhanced-product-card', [
            'discountPercentage' => $this->discountPercentage,
            'averageRating' => $this->averageRating,
            'reviewCount' => $this->reviewCount,
        ]);
    }
}
