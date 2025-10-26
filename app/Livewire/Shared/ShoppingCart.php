<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Data\Storefront\Shared\CartItemData;
use App\Models\AttributeValue;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * ShoppingCart
 *
 * Livewire component for ShoppingCart with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property bool $isOpen
 * @property-read Collection<int, array{id:int,product_id:int,variant_id:int|null,name:string,unit_price:float,quantity:int,total_price:float,snapshot:array<string,mixed>,image_url:?string}> $cartItems
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
     * Provide cart items as primitive arrays for safe Livewire hydration.
     *
     * @return Collection<int, array{id:int,product_id:int,variant_id:int|null,name:string,unit_price:float,quantity:int,total_price:float,snapshot:array<string,mixed>,image_url:?string}>
     */
    public function getCartItemsProperty(): Collection
    {
        return CartItem::with(['product', 'product.media'])
            ->where('session_id', Session::getId())
            ->get()
            ->map(static fn (CartItem $item): array => CartItemData::fromModel($item)->toArray());
    }

    /**
     * Handle getCartTotalProperty functionality with proper error handling.
     */
    public function getCartTotalProperty(): float
    {
        return (float) $this->cartItems->sum(
            static fn (array $item): float => (float) ($item['total_price'] ?? 0.0)
        );
    }

    /**
     * Handle getCartCountProperty functionality with proper error handling.
     */
    public function getCartCountProperty(): int
    {
        return (int) $this->cartItems->sum(
            static fn (array $item): int => (int) ($item['quantity'] ?? 0)
        );
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.shopping-cart');
    }
}
