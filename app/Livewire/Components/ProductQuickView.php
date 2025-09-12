<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class ProductQuickView extends Component
{
    use WithCart, WithNotifications;

    public ?Product $product = null;
    public bool $showModal = false;
    public int $quantity = 1;
    public ?int $selectedVariantId = null;
    public array $selectedAttributes = [];

    #[On('product-quick-view')]
    public function showProduct(int $productId): void
    {
        $this->product = Product::with(['media', 'brand', 'categories', 'variants.attributeValues', 'reviews'])
            ->findOrFail($productId);
        
        $this->quantity = 1;
        $this->selectedVariantId = null;
        $this->selectedAttributes = [];
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->product = null;
    }

    public function updatedSelectedAttributes(): void
    {
        if (!$this->product) return;

        // Find matching variant based on selected attributes
        $variant = $this->product->variants()
            ->whereHas('attributeValues', function ($query) {
                $query->whereIn('id', array_values($this->selectedAttributes));
            }, '=', count($this->selectedAttributes))
            ->first();

        $this->selectedVariantId = $variant?->id;
    }

    public function addToCart(): void
    {
        if (!$this->product) {
            $this->notifyError(__('ecommerce.product_not_found'));
            return;
        }

        $success = $this->addProductToCart(
            $this->product->id,
            $this->quantity,
            $this->selectedVariantId
        );

        if ($success) {
            $this->notifySuccess(__('ecommerce.added_to_cart'));
            $this->dispatch('cart-updated');
            $this->closeModal();
        } else {
            $this->notifyError(__('ecommerce.failed_to_add_to_cart'));
        }
    }

    public function addToWishlist(): void
    {
        if (!$this->product) return;

        $this->dispatch('wishlist-toggle', productId: $this->product->id);
        $this->notifySuccess(__('ecommerce.added_to_wishlist'));
    }

    public function getAverageRating(): float
    {
        if (!$this->product) return 0;
        
        return $this->product->reviews()
            ->where('is_approved', true)
            ->avg('rating') ?? 0;
    }

    public function getReviewsCount(): int
    {
        if (!$this->product) return 0;
        
        return $this->product->reviews()
            ->where('is_approved', true)
            ->count();
    }

    public function getCurrentPrice(): float
    {
        if (!$this->product) return 0;

        if ($this->selectedVariantId) {
            $variant = $this->product->variants->find($this->selectedVariantId);
            return $variant?->price ?? $this->product->price;
        }

        return $this->product->price;
    }

    public function render(): View
    {
        return view('livewire.components.product-quick-view');
    }
}



