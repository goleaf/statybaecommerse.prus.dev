<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Cart
 *
 * Livewire component for Cart with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property float $subtotal
 */
#[Layout('components.layouts.base')]
class Cart extends Component
{
    public float $subtotal = 0.0;

    /**
     * @var list<array<string, mixed>>
     */
    public array $items = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->refreshTotals();
    }

    /**
     * Handle getCartSession functionality with proper error handling.
     */
    private function getCartSession(): mixed
    {
        return null;
    }

    /**
     * Handle refreshTotals functionality with proper error handling.
     */
    public function refreshTotals(): void
    {
        $summary = app(CartService::class)->getSummary($this->resolveUserId(), $this->resolveSessionId());
        $this->subtotal = (float) ($summary['subtotal'] ?? 0.0);
        $this->items = is_array($summary['items'] ?? null) ? array_values($summary['items']) : [];
    }

    /**
     * Handle removeItem functionality with proper error handling.
     */
    public function removeItem(int $id, ?int $productId = null): void
    {
        $cartItem = $this->resolveCartItem($id, $productId);

        if ($cartItem !== null) {
            $productId = (int) $cartItem->product_id;
            $cartItem->delete();
            $this->removeFromSessionCart($productId);
        }

        $this->dispatch('cart-updated');
        $this->refreshTotals();
    }

    // Alias to keep shared cart item component working in different contexts
    /**
     * Handle removeToCart functionality with proper error handling.
     */
    public function removeToCart(int $id, ?int $productId = null): void
    {
        $this->removeItem($id, $productId);
    }

    /**
     * Handle updateItemQuantity functionality with proper error handling.
     */
    public function updateItemQuantity(int $id, int $quantity, ?int $productId = null): void
    {
        $quantity = max(0, $quantity);
        $cartItem = $this->resolveCartItem($id, $productId);

        if ($cartItem === null) {
            return;
        }

        if ($quantity === 0) {
            $productId = (int) $cartItem->product_id;
            $cartItem->delete();
            $this->removeFromSessionCart($productId);
        } else {
            $cartItem->updateQuantity($quantity);
        }

        $this->dispatch('cart-updated');
        $this->refreshTotals();
    }

    /**
     * Handle incrementItem functionality with proper error handling.
     */
    public function incrementItem(int $id, ?int $productId = null): void
    {
        $cartItem = $this->resolveCartItem($id, $productId);

        if ($cartItem === null) {
            return;
        }

        $cartItem->incrementQuantity(1);

        $this->dispatch('cart-updated');
        $this->refreshTotals();
    }

    /**
     * Handle decrementItem functionality with proper error handling.
     */
    public function decrementItem(int $id, ?int $productId = null): void
    {
        $cartItem = $this->resolveCartItem($id, $productId);

        if ($cartItem === null) {
            return;
        }

        if ((int) $cartItem->quantity <= 1) {
            $productId = (int) $cartItem->product_id;
            $cartItem->delete();
            $this->removeFromSessionCart($productId);
        } else {
            $cartItem->decrementQuantity(1);
        }

        $this->dispatch('cart-updated');
        $this->refreshTotals();
    }

    public function getItemThumbnail($item): ?string
    {
        if (is_object($item) && isset($item->image) && is_string($item->image) && $item->image !== '') {
            return $item->image;
        }

        if (is_array($item) && isset($item['image']) && is_string($item['image']) && $item['image'] !== '') {
            return $item['image'];
        }

        $model = $item->associatedModel ?? null;
        if ($model && method_exists($model, 'getImageUrl')) {
            return $model->getImageUrl('thumb')
                ?: $model->getImageUrl('preview')
                ?: $model->getImageUrl();
        }

        if ($model && method_exists($model, 'getFirstMediaUrl')) {
            return $model->getFirstMediaUrl(config('media.storage.thumbnail_collection'))
                ?: $model->getFirstMediaUrl(config('media.storage.thumbnail_collection'), 'thumb')
                ?: $model->getFirstMediaUrl(config('media.storage.thumbnail_collection'));
        }

        return null;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $sessionItems = collect($this->items)
            ->map(static fn (array $item): object => (object) $item);

        return view('livewire.pages.cart', ['items' => $sessionItems, 'subtotal' => $this->subtotal])->title(__('messages.your_cart'));
    }

    private function resolveCartItem(int $id, ?int $productId = null): ?CartItem
    {
        $sessionId = $this->resolveSessionId();
        $userId = $this->resolveUserId();

        $baseQuery = CartItem::withoutGlobalScopes()
            ->where(function (Builder $query) use ($sessionId, $userId): void {
                $query->where('session_id', $sessionId);

                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }
            });

        if ($id > 0) {
            $byCartItemId = (clone $baseQuery)
                ->where('id', $id)
                ->first();

            if ($byCartItemId instanceof CartItem) {
                return $byCartItemId;
            }
        }

        if ($productId !== null && $productId > 0) {
            $byProductId = (clone $baseQuery)
                ->where('product_id', $productId)
                ->first();

            if ($byProductId instanceof CartItem) {
                return $byProductId;
            }
        }

        return (clone $baseQuery)
            ->where('product_id', $id)
            ->first();
    }

    private function removeFromSessionCart(int $productId): void
    {
        $cart = session()->get('cart', []);

        if (! is_array($cart)) {
            return;
        }

        unset($cart[(string) $productId]);
        session()->put('cart', $cart);
    }

    private function resolveSessionId(): string
    {
        return (string) session()->getId();
    }

    private function resolveUserId(): ?int
    {
        $userId = auth()->id();

        return is_numeric($userId) ? (int) $userId : null;
    }
}
