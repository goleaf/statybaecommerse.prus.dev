<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class WishlistWidget extends Component
{
    public array $wishlistItems = [];
    public bool $showWishlist = false;
    public int $totalItems = 0;

    public function mount(): void
    {
        $this->loadWishlist();
    }

    #[On('wishlist-updated')]
    public function loadWishlist(): void
    {
        if (!auth()->check()) {
            $this->wishlistItems = session('wishlist', []);
        } else {
            /** @var User $user */
            $user = auth()->user();
            $this->wishlistItems = $user->wishlist()->with(['media', 'brand'])->get()->toArray();
        }
        
        $this->totalItems = count($this->wishlistItems);
    }

    public function toggleWishlist(int $productId): void
    {
        if (!auth()->check()) {
            $wishlist = session('wishlist', []);
            
            if (in_array($productId, $wishlist)) {
                $wishlist = array_filter($wishlist, fn($id) => $id !== $productId);
                $this->dispatch('wishlist-removed', productId: $productId);
            } else {
                $wishlist[] = $productId;
                $this->dispatch('wishlist-added', productId: $productId);
            }
            
            session(['wishlist' => array_values($wishlist)]);
        } else {
            /** @var User $user */
            $user = auth()->user();
            $product = Product::findOrFail($productId);
            
            if ($user->wishlist()->where('product_id', $productId)->exists()) {
                $user->wishlist()->detach($productId);
                $this->dispatch('wishlist-removed', productId: $productId);
            } else {
                $user->wishlist()->attach($productId);
                $this->dispatch('wishlist-added', productId: $productId);
            }
        }
        
        $this->loadWishlist();
        $this->dispatch('wishlist-updated');
    }

    public function removeFromWishlist(int $productId): void
    {
        $this->toggleWishlist($productId);
    }

    public function clearWishlist(): void
    {
        if (!auth()->check()) {
            session()->forget('wishlist');
        } else {
            /** @var User $user */
            $user = auth()->user();
            $user->wishlist()->detach();
        }
        
        $this->loadWishlist();
        $this->dispatch('wishlist-cleared');
    }

    public function toggleWishlistModal(): void
    {
        $this->showWishlist = !$this->showWishlist;
    }

    public function isInWishlist(int $productId): bool
    {
        if (!auth()->check()) {
            return in_array($productId, session('wishlist', []));
        }
        
        return collect($this->wishlistItems)->contains('id', $productId);
    }

    public function render(): View
    {
        return view('livewire.components.advanced-wishlist');
    }
}
