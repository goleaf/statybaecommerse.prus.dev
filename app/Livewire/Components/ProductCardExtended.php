<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Livewire\Attributes\On;
use Livewire\Component;
/**
 * ProductCardExtended
 * 
 * Livewire component for ProductCardExtended with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 * @property bool $showQuickView
 * @property bool $showCompare
 * @property bool $showWishlist
 * @property string $layout
 * @property bool $isInWishlist
 * @property bool $isInComparison
 */
final class ProductCardExtended extends Component
{
    public Product $product;
    public bool $showQuickView = false;
    public bool $showCompare = true;
    public bool $showWishlist = true;
    public string $layout = 'grid';
    // grid, list, minimal
    public bool $isInWishlist = false;
    public bool $isInComparison = false;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->checkWishlistStatus();
        $this->checkComparisonStatus();
    }
    /**
     * Handle addToCart functionality with proper error handling.
     * @return void
     */
    public function addToCart(): void
    {
        $this->dispatch('add-to-cart', ['product_id' => $this->product->id, 'quantity' => 1]);
        // Track analytics
        AnalyticsEvent::track('add_to_cart', ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'product_price' => $this->product->price]);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_added_to_cart', ['name' => $this->product->name])]);
    }
    /**
     * Handle toggleWishlist functionality with proper error handling.
     * @return void
     */
    public function toggleWishlist(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => __('translations.login_required_for_wishlist')]);
            return;
        }
        $wishlist = UserWishlist::where('user_id', auth()->id())->where('is_default', true)->first();
        if (!$wishlist) {
            $wishlist = UserWishlist::create(['user_id' => auth()->id(), 'name' => __('translations.my_wishlist'), 'is_default' => true]);
        }
        if ($this->isInWishlist) {
            $wishlist->removeProduct($this->product->id);
            $this->isInWishlist = false;
            $message = __('translations.product_removed_from_wishlist');
        } else {
            $wishlist->addProduct($this->product->id);
            $this->isInWishlist = true;
            $message = __('translations.product_added_to_wishlist');
        }
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
        $this->dispatch('wishlist-updated');
    }
    /**
     * Handle toggleComparison functionality with proper error handling.
     * @return void
     */
    public function toggleComparison(): void
    {
        $sessionId = session()->getId();
        $userId = auth()->id();
        $comparison = ProductComparison::where(function ($query) use ($sessionId, $userId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->where('product_id', $this->product->id)->first();
        if ($comparison) {
            $comparison->delete();
            $this->isInComparison = false;
            $message = __('translations.product_removed_from_comparison');
        } else {
            ProductComparison::create(['session_id' => $sessionId, 'user_id' => $userId, 'product_id' => $this->product->id]);
            $this->isInComparison = true;
            $message = __('translations.product_added_to_comparison');
        }
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
        $this->dispatch('comparison-updated');
    }
    /**
     * Handle quickView functionality with proper error handling.
     * @return void
     */
    public function quickView(): void
    {
        // Track analytics
        AnalyticsEvent::track('product_view', ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'view_type' => 'quick_view']);
        $this->dispatch('open-quick-view', ['product_id' => $this->product->id]);
    }
    /**
     * Handle viewProduct functionality with proper error handling.
     */
    public function viewProduct()
    {
        // Track analytics
        AnalyticsEvent::track('product_view', ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'view_type' => 'full_page']);
        return $this->redirect(route('product.show', $this->product));
    }
    /**
     * Handle refreshWishlistStatus functionality with proper error handling.
     * @return void
     */
    #[On('wishlist-updated')]
    public function refreshWishlistStatus(): void
    {
        $this->checkWishlistStatus();
    }
    /**
     * Handle refreshComparisonStatus functionality with proper error handling.
     * @return void
     */
    #[On('comparison-updated')]
    public function refreshComparisonStatus(): void
    {
        $this->checkComparisonStatus();
    }
    /**
     * Handle checkWishlistStatus functionality with proper error handling.
     * @return void
     */
    private function checkWishlistStatus(): void
    {
        if (!auth()->check()) {
            $this->isInWishlist = false;
            return;
        }
        $this->isInWishlist = WishlistItem::whereHas('wishlist', function ($query) {
            $query->where('user_id', auth()->id());
        })->where('product_id', $this->product->id)->exists();
    }
    /**
     * Handle checkComparisonStatus functionality with proper error handling.
     * @return void
     */
    private function checkComparisonStatus(): void
    {
        $sessionId = session()->getId();
        $userId = auth()->id();
        $this->isInComparison = ProductComparison::where(function ($query) use ($sessionId, $userId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->where('product_id', $this->product->id)->exists();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.product-card-extended');
    }
}