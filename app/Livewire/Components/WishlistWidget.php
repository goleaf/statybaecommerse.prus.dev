<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
/**
 * WishlistWidget
 * 
 * Livewire component for WishlistWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property array $wishlistItems
 * @property bool $showWishlist
 * @property int $totalItems
 */
final class WishlistWidget extends Component
{
    public array $wishlistItems = [];
    public bool $showWishlist = false;
    public int $totalItems = 0;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->loadWishlist();
    }
    /**
     * Handle loadWishlist functionality with proper error handling.
     * @return void
     */
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
    /**
     * Handle toggleWishlist functionality with proper error handling.
     * @param int $productId
     * @return void
     */
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
            $user->wishlist()->where('product_id', $productId)->existsOr(function () use ($user, $productId) {
                $user->wishlist()->attach($productId);
                $this->dispatch('wishlist-added', productId: $productId);
            }, function () use ($user, $productId) {
                $user->wishlist()->detach($productId);
                $this->dispatch('wishlist-removed', productId: $productId);
            });
        }
        $this->loadWishlist();
        $this->dispatch('wishlist-updated');
    }
    /**
     * Handle removeFromWishlist functionality with proper error handling.
     * @param int $productId
     * @return void
     */
    public function removeFromWishlist(int $productId): void
    {
        $this->toggleWishlist($productId);
    }
    /**
     * Handle clearWishlist functionality with proper error handling.
     * @return void
     */
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
    /**
     * Handle toggleWishlistModal functionality with proper error handling.
     * @return void
     */
    public function toggleWishlistModal(): void
    {
        $this->showWishlist = !$this->showWishlist;
    }
    /**
     * Handle isInWishlist functionality with proper error handling.
     * @param int $productId
     * @return bool
     */
    public function isInWishlist(int $productId): bool
    {
        if (!auth()->check()) {
            return in_array($productId, session('wishlist', []));
        }
        return collect($this->wishlistItems)->contains('id', $productId);
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.advanced-wishlist');
    }
}