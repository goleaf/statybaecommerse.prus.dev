<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ProductCard extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['brand', 'media']);
    }

    public function addToCart(): void
    {
        $this->dispatch('add-to-cart', productId: $this->product->id, quantity: 1);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Produktas pridėtas į krepšelį!',
        ]);
    }

    public function addToWishlist(): void
    {
        $this->dispatch('add-to-wishlist', productId: $this->product->id);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Produktas pridėtas į pageidavimų sąrašą!',
        ]);
    }

    public function getImageUrlProperty(): string
    {
        return $this->product->getFirstMediaUrl('images', 'thumb') 
            ?: asset('images/placeholder-product.png');
    }

    public function getCurrentPriceProperty(): float
    {
        $price = $this->product->sale_price ?? $this->product->price ?? 0.0;
        return (float) $price;
    }

    public function getOriginalPriceProperty(): ?float
    {
        if ($this->product->sale_price) {
            return (float) $this->product->price;
        }
        return null;
    }

    public function getDiscountPercentageProperty(): ?int
    {
        if (!$this->product->sale_price || !$this->product->price) {
            return null;
        }

        return (int) round((($this->product->price - $this->product->sale_price) / $this->product->price) * 100);
    }

    public function render(): View
    {
        return view('livewire.components.product-card');
    }
}
