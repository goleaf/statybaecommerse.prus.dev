<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\CartItem;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
/**
 * ShoppingCartWidget
 * 
 * Livewire component for ShoppingCartWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property bool $isOpen
 * @property string $discountCode
 * @property DiscountCode|null $appliedDiscount
 * @property array $cartSummary
 */
final class ShoppingCartWidget extends Component
{
    public bool $isOpen = false;
    #[Validate('nullable|string|max:50')]
    public string $discountCode = '';
    public ?DiscountCode $appliedDiscount = null;
    public array $cartSummary = [];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->calculateCartSummary();
    }
    /**
     * Handle refreshCart functionality with proper error handling.
     * @return void
     */
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $this->calculateCartSummary();
    }
    /**
     * Handle toggleCart functionality with proper error handling.
     * @return void
     */
    #[On('toggle-cart')]
    public function toggleCart(): void
    {
        $this->isOpen = !$this->isOpen;
    }
    /**
     * Handle addToCart functionality with proper error handling.
     * @param int $productId
     * @param int $quantity
     * @param array $options
     * @return void
     */
    public function addToCart(int $productId, int $quantity = 1, array $options = []): void
    {
        $product = Product::findOrFail($productId);
        $sessionId = Session::getId();
        // Check stock availability
        if ($product->manage_stock && $product->availableQuantity() < $quantity) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('translations.insufficient_stock')]);
            return;
        }
        $cartItem = CartItem::where('session_id', $sessionId)->where('product_id', $productId)->first();
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            // Check stock for updated quantity
            if ($product->manage_stock && $product->availableQuantity() < $newQuantity) {
                $this->dispatch('notify', ['type' => 'error', 'message' => __('translations.insufficient_stock_for_quantity')]);
                return;
            }
            $cartItem->update(['quantity' => $newQuantity, 'options' => array_merge($cartItem->options ?? [], $options)]);
        } else {
            CartItem::create(['session_id' => $sessionId, 'user_id' => auth()->id(), 'product_id' => $productId, 'quantity' => $quantity, 'price' => $product->sale_price ?? $product->price, 'options' => $options]);
        }
        $this->calculateCartSummary();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_added_to_cart')]);
    }
    /**
     * Handle updateQuantity functionality with proper error handling.
     * @param int $cartItemId
     * @param int $quantity
     * @return void
     */
    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cartItemId);
            return;
        }
        $cartItem = CartItem::where('id', $cartItemId)->where('session_id', Session::getId())->first();
        if (!$cartItem) {
            return;
        }
        // Check stock availability
        $product = $cartItem->product;
        if ($product->manage_stock && $product->availableQuantity() < $quantity) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('translations.insufficient_stock_for_quantity')]);
            return;
        }
        $cartItem->update(['quantity' => $quantity]);
        $this->calculateCartSummary();
        $this->dispatch('cart-updated');
    }
    /**
     * Handle removeItem functionality with proper error handling.
     * @param int $cartItemId
     * @return void
     */
    public function removeItem(int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)->where('session_id', Session::getId())->delete();
        $this->calculateCartSummary();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.item_removed_from_cart')]);
    }
    /**
     * Handle clearCart functionality with proper error handling.
     * @return void
     */
    public function clearCart(): void
    {
        CartItem::where('session_id', Session::getId())->delete();
        $this->appliedDiscount = null;
        $this->discountCode = '';
        $this->calculateCartSummary();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.cart_cleared')]);
    }
    /**
     * Handle applyDiscountCode functionality with proper error handling.
     * @return void
     */
    public function applyDiscountCode(): void
    {
        $this->validate();
        if (empty($this->discountCode)) {
            return;
        }
        $discount = DiscountCode::where('code', $this->discountCode)->where('is_enabled', true)->where('usage_limit', '>', 'used_count')->where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->first();
        if (!$discount) {
            $this->addError('discountCode', __('translations.invalid_discount_code'));
            return;
        }
        // Check if discount is applicable to current cart
        if (!$this->isDiscountApplicable($discount)) {
            $this->addError('discountCode', __('translations.discount_not_applicable'));
            return;
        }
        $this->appliedDiscount = $discount;
        $this->calculateCartSummary();
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.discount_applied')]);
    }
    /**
     * Handle removeDiscountCode functionality with proper error handling.
     * @return void
     */
    public function removeDiscountCode(): void
    {
        $this->appliedDiscount = null;
        $this->discountCode = '';
        $this->calculateCartSummary();
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.discount_removed')]);
    }
    /**
     * Handle isDiscountApplicable functionality with proper error handling.
     * @param DiscountCode $discount
     * @return bool
     */
    protected function isDiscountApplicable(DiscountCode $discount): bool
    {
        $cartTotal = $this->getCartSubtotal();
        // Check minimum amount
        if ($discount->minimum_amount && $cartTotal < $discount->minimum_amount) {
            return false;
        }
        // Check maximum amount
        if ($discount->maximum_amount && $cartTotal > $discount->maximum_amount) {
            return false;
        }
        // Add more complex discount logic here
        return true;
    }
    /**
     * Handle calculateCartSummary functionality with proper error handling.
     * @return void
     */
    protected function calculateCartSummary(): void
    {
        $cartItems = $this->getCartItems();
        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
        $discountAmount = 0;
        $taxAmount = 0;
        $shippingAmount = 0;
        // Apply discount if available
        if ($this->appliedDiscount) {
            $discountAmount = $this->calculateDiscountAmount($subtotal);
        }
        // Calculate tax (example: 21% VAT for Lithuania)
        $taxRate = app_setting('tax_rate', 0.21);
        $taxAmount = ($subtotal - $discountAmount) * $taxRate;
        // Calculate shipping (simplified)
        $shippingAmount = $subtotal > app_setting('free_shipping_threshold', 50) ? 0 : app_setting('shipping_cost', 5);
        $total = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
        $this->cartSummary = ['items_count' => $cartItems->sum('quantity'), 'subtotal' => $subtotal, 'discount_amount' => $discountAmount, 'tax_amount' => $taxAmount, 'shipping_amount' => $shippingAmount, 'total' => $total];
    }
    /**
     * Handle calculateDiscountAmount functionality with proper error handling.
     * @param float $subtotal
     * @return float
     */
    protected function calculateDiscountAmount(float $subtotal): float
    {
        if (!$this->appliedDiscount) {
            return 0;
        }
        return match ($this->appliedDiscount->type) {
            'percentage' => $subtotal * ($this->appliedDiscount->value / 100),
            'fixed' => min($this->appliedDiscount->value, $subtotal),
            default => 0,
        };
    }
    /**
     * Handle getCartSubtotal functionality with proper error handling.
     * @return float
     */
    protected function getCartSubtotal(): float
    {
        return $this->getCartItems()->sum(fn($item) => $item->price * $item->quantity);
    }
    /**
     * Handle getCartItemsProperty functionality with proper error handling.
     */
    public function getCartItemsProperty()
    {
        return $this->getCartItems();
    }
    /**
     * Handle getCartItems functionality with proper error handling.
     */
    protected function getCartItems()
    {
        return CartItem::where('session_id', Session::getId())->with(['product.media', 'product.brand'])->get();
    }
    /**
     * Handle proceedToCheckout functionality with proper error handling.
     * @return void
     */
    public function proceedToCheckout(): void
    {
        if ($this->cartItems->isEmpty()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('translations.cart_is_empty')]);
            return;
        }
        $this->redirect(route('checkout.index', app()->getLocale()));
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.shopping-cart', ['cartItems' => $this->cartItems, 'cartSummary' => $this->cartSummary]);
    }
}