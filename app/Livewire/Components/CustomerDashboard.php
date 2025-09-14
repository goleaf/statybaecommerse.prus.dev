<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final /**
 * CustomerDashboard
 * 
 * Livewire component for reactive frontend functionality.
 */
class CustomerDashboard extends Component
{
    use WithPagination;

    public User $user;

    public array $stats = [];

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_orders' => $this->user->orders()->count(),
            'completed_orders' => $this->user->orders()->where('status', 'completed')->count(),
            'pending_orders' => $this->user->orders()->where('status', 'pending')->count(),
            'total_spent' => $this->user->orders()->where('status', 'completed')->sum('total'),
            'wishlist_items' => $this->user->wishlist()->count(),
            'reviews_written' => $this->user->reviews()->count(),
            'member_since' => $this->user->created_at->format('Y'),
            'last_order' => $this->user->orders()->latest()->first()?->created_at?->diffForHumans(),
        ];
    }

    public function getRecentOrdersProperty()
    {
        return $this->user->orders()
            ->with(['items.product'])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getWishlistItemsProperty()
    {
        return $this->user->wishlist()
            ->with(['media', 'brand'])
            ->limit(6)
            ->get();
    }

    public function getRecommendedProductsProperty()
    {
        // Simple recommendation based on previous purchases
        $purchasedCategories = $this->user->orders()
            ->with('items.product.categories')
            ->where('status', 'completed')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('product.categories')
            ->flatten()
            ->pluck('id')
            ->unique();

        return Product::query()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->whereHas('categories', function ($query) use ($purchasedCategories) {
                $query->whereIn('categories.id', $purchasedCategories);
            })
            ->whereNotIn('id', $this->user->orders()
                ->with('items')
                ->get()
                ->pluck('items')
                ->flatten()
                ->pluck('product_id')
            )
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    public function removeFromWishlist(int $productId): void
    {
        $this->user->wishlist()->detach($productId);
        $this->loadStats();

        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('ecommerce.removed_from_wishlist'),
        ]);
    }

    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);

        // Add to cart logic here
        session()->push('cart', [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ]);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('ecommerce.added_to_cart'),
        ]);
    }

    public function render(): View
    {
        return view('livewire.components.customer-dashboard');
    }
}
