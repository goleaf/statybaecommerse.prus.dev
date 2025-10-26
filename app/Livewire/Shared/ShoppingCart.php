<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Data\Cart\CartLineItemData;
use App\Models\AttributeValue;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * ShoppingCart
 *
 * Livewire component for ShoppingCart with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property bool $isOpen
 * @property-read EloquentCollection<int, CartItem> $cartItems
 */
final class ShoppingCart extends Component
{
    public bool $isOpen = false;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Initialize cart
    }

    /**
     * Handle addToCart functionality with proper error handling.
     */
    #[On('add-to-cart')]
    public function addToCart(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        $product = Product::findOrFail($productId);
        $variant = null;

        if ($variantId !== null) {
            $variant = ProductVariant::query()
                ->where('product_id', $productId)
                ->findOrFail($variantId);
        }

        $sessionId = Session::getId();
        $unitPrice = $variant
            ? (float) $variant->getCurrentPrice()
            : (float) ($product->sale_price ?? $product->price);

        $cartItemQuery = CartItem::query()
            ->where('session_id', $sessionId)
            ->where('product_id', $productId);

        if ($variant) {
            $cartItemQuery->where('variant_id', $variant->id);
        } else {
            $cartItemQuery->whereNull('variant_id');
        }

        $cartItem = $cartItemQuery->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;

            $cartItem->update([
                'quantity'    => $newQuantity,
                'unit_price'  => $unitPrice,
                'price'       => $unitPrice,
                'total_price' => round($unitPrice * $newQuantity, 2),
            ]);
        } else {
            $variantAttributes = [];
            $snapshotName = $product->name;

            if ($variant) {
                $snapshotName = $variant->name ?? $product->name;

                /** @var EloquentCollection<int, AttributeValue> $attributeValues */
                $attributeValues = $variant->attributes()->with('attribute')->get();

                if ($attributeValues->isNotEmpty()) {
                    $variantAttributes = $attributeValues
                        ->mapWithKeys(static function (AttributeValue $value): array {
                            $attribute = $value->attribute;

                            if ($attribute === null || $attribute->name === null) {
                                return [];
                            }

                            return [
                                (string) $attribute->name => (string) $value->value,
                            ];
                        })
                        ->toArray();

                    $snapshotName .= ' (' . collect($variantAttributes)
                        ->filter(static fn ($value, $key): bool => $key !== '' && $value !== null)
                        ->map(static fn ($value, $key): string => sprintf('%s: %s', (string) $key, (string) $value))
                        ->implode(', ') . ')';
                }
            }

            $productSnapshot = [
                'name'  => $snapshotName,
                'price' => $unitPrice,
                'sku'   => $variant?->sku ?? $product->sku ?? null,
            ];

            if ($variant) {
                $productSnapshot['variant_id'] = $variant->id;

                if (! empty($variantAttributes)) {
                    $productSnapshot['variant_attributes'] = $variantAttributes;
                }
            }

            CartItem::create([
                'session_id'         => $sessionId,
                'user_id'            => auth()->id(),
                'product_id'         => $productId,
                'variant_id'         => $variant?->id,
                'product_variant_id' => $variant?->id,
                'quantity'           => $quantity,
                'unit_price'         => $unitPrice,
                'total_price'        => round($unitPrice * $quantity, 2),
                'price'              => $unitPrice,
                'product_snapshot'   => $productSnapshot,
            ]);
        }

        $this->dispatch('cart-updated');
    }

    /**
     * Handle updateQuantity functionality with proper error handling.
     */
    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cartItemId);

            return;
        }
        CartItem::where('id', $cartItemId)->where('session_id', Session::getId())->update(['quantity' => $quantity]);
        $this->dispatch('cart-updated');
    }

    /**
     * Handle removeItem functionality with proper error handling.
     */
    public function removeItem(int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)->where('session_id', Session::getId())->delete();
        $this->dispatch('cart-updated');
    }

    /**
     * Handle clearCart functionality with proper error handling.
     */
    public function clearCart(): void
    {
        CartItem::where('session_id', Session::getId())->delete();
        $this->dispatch('cart-updated');
    }

    /**
     * Handle toggleCart functionality with proper error handling.
     */
    public function toggleCart(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    /**
     * Handle refreshCart functionality with proper error handling.
     */
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        // This will trigger a re-render
    }

    /**
     * Handle getCartItemsProperty functionality with proper error handling.
     */
    /**
     * @return EloquentCollection<int, CartItem>
     */
    public function getCartItemsProperty(): EloquentCollection
    {
        /** @var EloquentCollection<int, CartItem> $items */
        $items = CartItem::with(['product', 'product.media'])
            ->where('session_id', Session::getId())
            ->get();

        return $items;
    }

    /**
     * @return array<int, array{id:int,name:string,quantity:int,unitPrice:float,totalPrice:float,thumbnailUrl:?string}>
     */
    public function getCartLinesProperty(): array
    {
        return $this->cartItems
            ->map(static function (CartItem $item): CartLineItemData {
                $product = $item->product;
                $thumbnail = $product?->getFirstMediaUrl();

                return new CartLineItemData(
                    id: (int) $item->id,
                    name: (string) ($product?->name ?? data_get($item->product_snapshot, 'name', '')),
                    quantity: (int) $item->quantity,
                    unitPrice: (float) $item->price,
                    totalPrice: (float) $item->total_price,
                    thumbnailUrl: is_string($thumbnail) && $thumbnail !== '' ? $thumbnail : null,
                );
            })
            ->map(static fn (CartLineItemData $line): array => $line->toArray())
            ->values()
            ->all();
    }

    /**
     * Handle getCartTotalProperty functionality with proper error handling.
     */
    public function getCartTotalProperty(): float
    {
        return $this->cartItems->sum(static function (CartItem $item): float {
            return (float) $item->total_price;
        });
    }

    /**
     * Handle getCartCountProperty functionality with proper error handling.
     */
    public function getCartCountProperty(): int
    {
        return (int) $this->cartItems->sum('quantity');
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.shopping-cart');
    }
}
